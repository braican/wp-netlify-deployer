<?php
/**
 * Netlify Deploybot
 *
 * @package   NetlifyDeploybot\Admin
 * @author    Nick Braica
 * @license   GPL-2.0+
 * @link      https://www.braican.com
 * @copyright 2018 Nick Braica
 */

namespace NetlifyDeploybot;

class Admin {

    /**
     * The unique instance of the Site class.
     * 
     * @var NetlifyDeploybot\Admin
     */
    private static $instance;


    /**
     * The slug for the menu page.
     * 
     * @var string
     */
    private $menu_page_slug = 'deploybot-settings';


    /**
     * The name of the settings section.
     * 
     * @var string
     */
    private $option_group = 'deploybot_settings';


    /**
     * Checks to see if the admin has been loaded already.
     * 
     * @var boolean
     */
    private $loaded = false;


    /**
     * Gets the instance of the class.
     * 
     * @return NetlifyDeploybot\Admin
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * This is empty to prevent anything but a singleton.
     */
    private function __construct() {}


    /**
     * Trigger admin hooks to create an admin page and settings.
     * 
     * @return void
     */
    public function trigger_hooks() {
        add_action('admin_menu', array($this, 'setup_menu_page'));
        add_action('admin_init', array($this, 'setup_menu_settings'));
    }


    /**
     * Sets up a menu page for the deploy settings.
     * 
     * @return void
     */
    public function setup_menu_page() {
        add_menu_page(
            'Netlify Deploybot Settings',
            'Deploybot',
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
            'Deploybot Settings',
            array($this, 'settings_markup'),
            $this->menu_page_slug
        );

        add_settings_field(
            'build_hook_url',
            'Build Hook',
            array($this, 'field_markup'),
            $this->menu_page_slug,
            $this->option_group,
            array(
                'id' => 'build_hook_url'
            )
        );
    }


    // -------------------------------------------------
    //
    // Getters
    //
    // -------------------------------------------------

    /**
     * Returns the loaded status of the admin.
     * 
     * @return boolean
     */
    public function is_loaded() {
        return $this->loaded;
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
        <h1 class="wp-heading-inline">Netlify Deploybot</h1>
	        <form action="options.php" method="post">
	        <?php
                settings_errors();

                settings_fields($this->option_group);
                
                // add_settings_field callbacks
                do_settings_sections($this->menu_page_slug);

	            submit_button(); 
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
        echo '';
    }

    /**
     * Creates the markup for the admin page.
     * 
     * @return void
     */
    public function field_markup($args) {
        $options = get_option($this->option_group);
        $field = $args['id'];
        $value = $options[$field] ?? '';
        $deploy_button = $value ? '<button>Deploy</button>' : '';
        echo sprintf(
            '<input type="url" class="regular-text" name="%1$s[%2$s]" id="%2$s" value="%3$s">%4$s',
            $this->option_group,
            $field,
            $value,
            $deploy_button
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
}
