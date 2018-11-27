<?php
/**
 * Netlify Deploybot
 *
 * @package   NetlifyDeploybot
 * @author    Nick Braica
 * @license   GPL-2.0+
 * @link      https://www.braican.com
 * @copyright 2018 Nick Braica
 *
 * @wordpress-plugin
 * Plugin Name:       Netlify Deploybot
 * Description:       Trigger Netlify deploys with ease.
 * Version:           0.0.1
 * Author:            Nick Braica
 * Author URI:        https://www.braican.com
 * Text Domain:       netlify-deploybot
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/braican/netlify-deploybot
 */

namespace NetlifyDeploybot;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

/**
 * Plugin version.
 */
define('NETLIFY_DEPLOYBOT_VERSION', '0.0.1');

// Load the autoloader.
include_once plugin_dir_path(__FILE__) . 'lib/autoloader.php';

// Main class
class NetlifyDeploybot {

    /**
     * The unique instance of this class.
     * 
     * @var NetlifyDeploybot\NetlifyDeploybot
     */
    private static $instance;

    /**
     * Unique identifier for the plugin.
     * 
     * @var string
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     * 
     * @var string
     */
    protected $version;

    /**
     * Get the instance of the class.
     * 
     * @return NetlifyDeploybot\NetlifyDeploybot
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Define core functionality of the plugin. Set some identifying information, load
     *  dependencies, and set hooks.
     */
    private function __construct() {
        if ( defined( 'NETLIFY_DEPLOYBOT_VERSION' ) ) {
			$this->version = NETLIFY_DEPLOYBOT_VERSION;
		} else {
			$this->version = '0.0.1';
        }
        
        $this->plugin_name = 'netlify-deploybot';

        $this->set_admin_hooks();
    }

    private function set_admin_hooks() {
        $Admin = Admin::get_instance();
        $Admin->trigger_hooks();
    }
}

/**
 * Init the plugin.
 * 
 * @return void
 */
function run() {
    $netlify_deploybot = NetlifyDeploybot::get_instance();
}
run();