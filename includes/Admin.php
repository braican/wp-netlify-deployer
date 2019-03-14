<?php
/**
 * Netlify Deployer
 *
 * @package   NetlifyDeployer
 * @author    Nick Braica
 * @license   MIT License
 * @link      https://www.braican.com
 * @copyright 2019 Nick Braica
 */

namespace NetlifyDeployer;

/**
 * Class to handle Administrative settings within WordPress.
 */
class Admin {
	/**
	 * The unique instance of the Site class.
	 *
	 * @var NetlifyDeployer\Admin
	 */
	private static $instance;


	/**
	 * The slug for the menu page.
	 *
	 * @var string
	 */
	private $menu_page_slug = 'deployer-settings';


	/**
	 * The name of the settings section for webhooks.
	 *
	 * @var string
	 */
	public $webhook_group = 'deployer_settings';


	/**
	 * The name of the settings section for Netlify settings.
	 *
	 * @var string
	 */
	public $netlify_group = 'deployer_netlify_settings';

	/**
	 * The name of the settings section for Netlify settings.
	 *
	 * @var array
	 */
	public $webhook_settings = array();

	/**
	 * The name of the settings section for Netlify settings.
	 *
	 * @var array
	 */
	public $netlify_settings = array();


	/**
	 * List of whitelisted post types that increment the undeployed changes counter on save.
	 *
	 * @var array
	 */
	private $incrementable_types = array(
		'post',
		'page',
	);


	/**
	 * Gets the instance of the class.
	 *
	 * @return NetlifyDeployer\Admin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * This is private.
	 */
	private function __construct() {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			// Set up the data from the options table.
			$this->webhook_settings = get_option( $this->webhook_group );
			$this->netlify_settings = get_option( $this->netlify_group );

			$this->trigger_hooks();
		}
	}


	/**
	 * Trigger admin hooks to create an admin page and settings.
	 *
	 * @return void
	 */
	private function trigger_hooks() {
		// Enqueue.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		// Build the options page.
		add_action( 'admin_menu', array( $this, 'setup_menu_page' ) );
		add_action( 'admin_init', array( $this, 'setup_menu_settings' ) );
		add_action( 'save_post', array( $this, 'increment_saves_since_deploy' ) );
	}


	/**
	 * Add stylesheets to the admin.
	 *
	 * @return void
	 */
	public function admin_styles() {
		wp_enqueue_style( 'netlify-deployer-admin-styles', NETLIFY_DEPLOYER_DIRECTORY_URI . 'static/build/admin.css' );
	}


	/**
	 * Add scripts to the admin.
	 *
	 * @return void
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'netlify-deployer-admin-scripts', NETLIFY_DEPLOYER_DIRECTORY_URI . 'static/build/deployer.js', array( 'jquery' ), '1.0' );
	}


	/**
	 * Sets up a menu page for the deploy settings.
	 *
	 * @return void
	 */
	public function setup_menu_page() {
		add_menu_page(
			'Netlify Deployer Settings',
			'Deployer',
			'manage_options',
			$this->menu_page_slug,
			array( $this, 'menu_page_markup' )
		);
	}


	/**
	 * Create settings sections and fields to add to our menu page.
	 *
	 * @return void
	 */
	public function setup_menu_settings() {
		register_setting(
			$this->webhook_group,
			$this->webhook_group,
			array(
				'sanitize_callback' => array( $this, 'validate_settings' ),
			)
		);

		register_setting(
			$this->netlify_group,
			$this->netlify_group,
			array(
				'sanitize_callback' => array( $this, 'validate_settings' ),
			)
		);

		add_settings_section(
			$this->webhook_group,
			'Webhooks',
			function() {
				echo '<p>Use the following fields to add build hooks from Netlify that can be used to trigger a deployment to the respective environments.</p>';
			},
			$this->menu_page_slug
		);

		add_settings_field(
			'build_hook_url',
			'Build Hook',
			array( $this, 'build_hook_url_markup' ),
			$this->menu_page_slug,
			$this->webhook_group,
			array(
				'key'          => 'build_hook_url',
				'status_badge' => true,
			)
		);

		add_settings_field(
			'build_hook_url_staging',
			'Build Hook - Staging',
			array( $this, 'build_hook_url_markup' ),
			$this->menu_page_slug,
			$this->webhook_group,
			array( 'key' => 'build_hook_url_staging' )
		);

		/**
		 * Netlify group.
		 */

		add_settings_section(
			$this->netlify_group,
			'Netlify Settings',
			function() {
				echo '';
			},
			$this->menu_page_slug
		);

		add_settings_field(
			'netlify_site_id',
			'Site ID',
			array( $this, 'deployer_basic_text_field' ),
			$this->menu_page_slug,
			$this->netlify_group,
			array(
				'key'       => 'netlify_site_id',
				'help_text' => 'This can be found in your Netlify project by going to the Settings > General page and finding the "API ID" value in the Site Information.',
			)
		);

		add_settings_field(
			'netlify_deploy_admin',
			'Netlify Deployment Admin Link',
			array( $this, 'deployer_basic_text_field' ),
			$this->menu_page_slug,
			$this->netlify_group,
			array(
				'key'       => 'netlify_deploy_admin',
				'help_text' => 'This should be set to the link to the "Deploys" page in your Netlify project.',
			)
		);
	}


	/**
	 * When a page is saved, increment the number of saves since the last deploy.
	 *
	 * @param int $post_id WP post being saved.
	 *
	 * @return void
	 */
	public function increment_saves_since_deploy( $post_id ) {
		if ( wp_is_post_revision( $post_id ) || ! empty( $_POST ) ) {
			return;
		}

		$options   = $this->webhook_settings;
		$post_type = get_post_type( $post_id );

		if ( empty( $options ) || ! isset( $options['build_hook_url'] ) || ! $this->type_increment_saves( $post_type ) ) {
			return;
		}

		if ( isset( $options['undeployed_changes'] ) ) {
			$options['undeployed_changes'] += 1;
		} else {
			$options['undeployed_changes'] = 1;
		}

		update_option( $this->webhook_group, $options );
	}



	// -------------------------------------------------
	//
	// Markup
	//
	// -------------------------------------------------

	/**
	 * Creates the markup for the admin page.
	 *
	 * @return void
	 */
	public function menu_page_markup() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Netlify Deployer</h1>
			<p>Deployments to Netlify made simple.</p>
			<form action="options.php" method="post" class="netlify-deployer">
			<?php
				settings_errors();
				settings_fields( $this->webhook_group );
				settings_fields( $this->netlify_group );
				do_settings_sections( $this->menu_page_slug );
				submit_button( 'Save' );
			?>
			</form>
		</div>
		<?php
	}


	/**
	 * Creates the markup for a simple text field.
	 *
	 * @param array $args Object containing some optional arguments for the text field.
	 * @arg string $key
	 * @arg string $help_text
	 *
	 * @return void
	 */
	public function deployer_basic_text_field( $args ) {
		$options   = $this->netlify_settings;
		$value     = $options[ $args['key'] ] ?? '';
		$help_text = isset( $args['help_text'] ) ? '<p class="description">' . $args['help_text'] . '</p>' : '';

		echo sprintf(
			'<input type="text" class="regular-text" name="%1$s[%2$s]" id="%2$s" value="%3$s">%4$s',
			$this->netlify_group,
			$args['key'],
			$value,
			$help_text
		);
	}


	/**
	 * Creates the markup for the `build_hook_url` field.
	 *
	 * @param array $args Object containing some optional arguments for the text field.
	 * @arg string  $key
	 * @arg boolean $status_badge
	 *
	 * @return void
	 */
	public function build_hook_url_markup( $args ) {
		$value              = $this->webhook_settings[ $args['key'] ] ?? '';
		$undeployed_changes = $this->webhook_settings['undeployed_changes'] ?? 0;
		$save_label         = 1 == $undeployed_changes ? 'save' : 'saves';

		// Status badge.
		$status_badge = '';

		// Deploy actions.
		$changes_label = $undeployed_changes > 0 ? "<p class='change-count'>$undeployed_changes $save_label since last deployment.</p>" : '';
		$deploy_button = $value ? '<button class="js-deployer deployer">Deploy</button><span class="netlify-deployer-loader"></span>' : '';

		if ( isset( $args['status_badge'] ) ) {
			$status_badge = sprintf(
				'<div style="margin-top: 1em"><p style="margin-bottom: .5em;">Current state of the latest production deploy:</p><a target="_blank" href="%2$s"><img src="%1$s"></a></div>',
				$args['status_badge'],
				'https://app.netlify.com/sites/indigo-technology/deploys'
			);
		}

		$input_field = sprintf(
			'<input type="url" class="regular-text js-deploy-hook-string" name="%1$s[%2$s]" id="%2$s" value="%3$s">',
			$this->webhook_group,
			$args['key'],
			$value
		);

		$deploy_actions = sprintf(
			'<div>%s%s</div>',
			$changes_label,
			$deploy_button
		);

		if ( isset( $args['status_badge'] ) && $args['status_badge'] && $this->netlify_settings['netlify_site_id'] ) {
			$site_id              = $this->netlify_settings['netlify_site_id'];
			$deploy_admin_link    = $this->netlify_settings['netlify_deploy_admin'];
			$status_badge_img_src = "https://api.netlify.com/api/v1/badges/$site_id/deploy-status";

			$status_badge_img = sprintf( '<img src="%s">', $status_badge_img_src );

			if ( $deploy_admin_link ) {
				$status_badge_img = sprintf( '<a target="_blank" href="%s">%s</a>', $deploy_admin_link, $status_badge_img );
			}

			$status_badge = sprintf(
				'<div><p style="margin: 1em 0 .5em;">%s</p>%s</div>',
				'Current state of your latest production deploy:',
				$status_badge_img
			);
		}

		echo sprintf(
			'<div class="js-netlify-deployer-actions">%1$s%2$s%3$s</div>',
			$input_field,
			$deploy_actions,
			$status_badge
		);
	}


	// -------------------------------------------------
	//
	// Validators
	//
	// -------------------------------------------------

	/**
	 * Validate the settings fields.
	 *
	 * @param array $input The input values.
	 *
	 * @return array Filtered and sanitized input values.
	 */
	public function validate_settings( $input ) {
		if ( empty( $input ) ) {
			return $input;
		}

		$new_input = false;
		$options   = $this->webhook_settings;

		foreach ( $input as $key => $val ) {
			if ( 'build_hook_url' === $key && ! empty( $val ) && filter_var( $val, FILTER_VALIDATE_URL ) === false ) {
				add_settings_error( 'build_hook_url', 'invalid-url', 'You must supply a valid url.', 'error' );
				$new_input[ $key ] = $options['build_hook_url'];
			} elseif ( 'build_hook_url_staging' === $key && ! empty( $val ) && filter_var( $val, FILTER_VALIDATE_URL ) === false ) {
				add_settings_error( 'build_hook_url_staging', 'invalid-url', 'You must supply a valid url.', 'error' );
				$new_input[ $key ] = $options['build_hook_url_staging'];
			} else {
				$new_input[ $key ] = sanitize_text_field( $val );
			}
		}

		return $new_input;
	}


	// -------------------------------------------------
	//
	// Util
	//
	// -------------------------------------------------

	/**
	 * Checks to see if a save of the given post type should increment the undeployed changes.
	 *
	 * @param string $post_type The content type that should be checked.
	 *
	 * @return boolean True if the post type should increment, false if not.
	 */
	private function type_increment_saves( $post_type ) {
		return in_array( $post_type, $this->incrementable_types );
	}
}
