<?php
/**
 * Netlify Deployer
 *
 * @package   NetlifyDeployer
 * @author    Nick Braica
 * @license   MIT License
 * @link      https://www.braican.com
 * @copyright 2018 Nick Braica
 */

namespace NetlifyDeployer;

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
     * The name of the settings section.
     * 
     * @var string
     */
    public $option_group = 'deployer_settings';


    /**
     * List of whitelisted post types that increment the undeployed changes counter on save.
     * 
     * @var array
     */
    private $incrementable_types = array(
        'post',
        'page'
    );


    /**
     * Gets the instance of the class.
     * 
     * @return NetlifyDeployer\Admin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * This is private.
     */
    private function __construct() {
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
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
        add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        // Build the options page.
        add_action('admin_menu', array($this, 'setup_menu_page'));
        add_action('admin_init', array($this, 'setup_menu_settings'));
        add_action('save_post', array($this, 'increment_saves_since_deploy'));
    }


    /**
     * Add stylesheets to the admin.
     * 
     * @return void
     */
    public function admin_styles() {
        wp_enqueue_style('netlify-deployer-admin-styles', NETLIFY_DEPLOYER_DIRECTORY_URI . 'static/build/admin.css');
    }


    /**
     * Add scripts to the admin.
     * 
     * @return void
     */
    public function admin_scripts() {
        wp_enqueue_script( 'netlify-deployer-admin-scripts', NETLIFY_DEPLOYER_DIRECTORY_URI . 'static/build/deployer.js', array('jquery'), '1.0' );
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
            array($this, 'menu_page_markup')
        );
    }


    /**
     * Create settings sections and fields to add to our menu page.
     * 
     * @return void
     */
    public function setup_menu_settings() {
        register_setting($this->option_group, $this->option_group, array(
            'sanitize_callback' => array($this, 'validate_settings')
        ));

        add_settings_section(
            $this->option_group,
            'Webhook Settings',
            array($this, 'settings_markup'),
            $this->menu_page_slug
        );

        add_settings_field(
            'build_hook_url',
            'Build Hook',
            array($this, 'build_hook_url_markup'),
            $this->menu_page_slug,
            $this->option_group
        );
    }


    /**
     * When a page is saved, increment the number of saves since the last deploy.
     * 
     * @param int  $post_id  WP post being saved.
     * 
     * @return void
     */
    public function increment_saves_since_deploy($post_id) {
        if (wp_is_post_revision($post_id) || !empty($_POST)) {
            return;
        }

        $options = get_option($this->option_group);
        $post_type = get_post_type($post_id);
        
        if (empty($options) || !isset($options['build_hook_url']) || !$this->type_increment_saves($post_type)) {
            return;
        }

        if (isset($options['undeployed_changes'])) {
            $options['undeployed_changes'] += 1;
        } else {
            $options['undeployed_changes'] = 1;
        }
        
        update_option($this->option_group, $options);
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
	        <form action="options.php" method="post" class="netlify-deployer">
	        <?php
                settings_errors();
                settings_fields($this->option_group);
                do_settings_sections($this->menu_page_slug);
	            submit_button('Save Build Hook');
	        ?>          
	        </form>
		</div>
    <?php
    }

    /**
     * Creates the markup for the admin page.
     * 
     * @return void
     */
    public function settings_markup() {
        echo '<p>Netlify deployments made simple. Set up a build hook here to trigger a deploy to your Netlify site.</p>';
    }

    /**
     * Creates the markup for the `build_hook_url` field.
     * 
     * @return void
     */
    public function build_hook_url_markup() {
        $options = get_option($this->option_group);
        $value = $options['build_hook_url'] ?? '';
        $undeployed_changes = $options['undeployed_changes'] ?? 0;

        $save_label = $undeployed_changes == 1 ? 'save' : 'saves';
        $changes_label = $undeployed_changes > 0 ? "<p class='change-count'>$undeployed_changes $save_label since last deployment.</p>" : '';
        $deploy_button = $value ? '<button class="js-deployer deployer">Deploy</button><span class="netlify-deployer-loader"></span>' : '';

        $deploy_actions = sprintf(
            '<div>%s%s</div>',
            $changes_label,
            $deploy_button
        );

        echo sprintf(
            '<div class="js-netlify-deployer-actions"><input type="url" class="regular-text" name="%1$s[%2$s]" id="%2$s" value="%3$s">%4$s</div>',
            $this->option_group,
            'build_hook_url',
            $value,
            $deploy_actions
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
     * @param array  $input  The input values.
     * 
     * @return array Filtered and sanitized input values.
     */
    public function validate_settings($input) {
        if (empty($input)) {
            return $input;
        }

        $new_input = false;
        $options = get_option($this->option_group);

        foreach ($input as $key => $val) {
            if ($key === 'build_hook_url' && !empty($val) && filter_var($val, FILTER_VALIDATE_URL) === FALSE) {
                add_settings_error('build_hook_url', 'invalid-url', 'You must supply a valid url.', 'error');
                $new_input[$key] = $options['build_hook_url'];
            } else {
                $new_input[$key] = sanitize_text_field( $val );
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
     * @param string  $post_type  The content type that should be checked.
     * 
     * @return boolean True if the post type should increment, false if not.
     */
    private function type_increment_saves($post_type) {
        return in_array($post_type, $this->incrementable_types);
    }
}
