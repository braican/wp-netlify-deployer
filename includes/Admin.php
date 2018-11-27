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
     * The name of the settings group.
     * 
     * @var string
     */
    private $option_group = 'deploybot';


    /**
     * The name of the settings section.
     * 
     * @var string
     */
    private $settings_section = 'deploybot_settings';

    
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
        add_settings_section(
            $this->settings_section,
            'Deploybot Settings',
            array($this, 'settings_markup'),
            $this->menu_page_slug
        );

        add_settings_field(
            'build_hook_url',
            'Build Hook',
            array($this, 'build_hook_markup'),
            $this->menu_page_slug,
            $this->settings_section
        );

        register_setting($this->option_group, 'build_hook_url');
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
	        <form method="post">
	        <?php
	            settings_fields($this->option_group);
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
        echo '<p>The settings</p>';
    }

    /**
     * Creates the markup for the admin page.
     * 
     * @return void
     */
    public function build_hook_markup() {
        echo sprintf('<input type="text" name="build_hook_url" id="build_hook_url", value="%s">', get_option('build_hook_url'));
    }
}
