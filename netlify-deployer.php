<?php
/**
 * Netlify Deployer
 *
 * @package   NetlifyDeployer
 * @author    Nick Braica
 * @license   MIT License
 * @link      https://www.braican.com
 * @copyright 2019 Nick Braica
 *
 * @wordpress-plugin
 * Plugin Name:       Netlify Deployer
 * Description:       Trigger Netlify deploys with ease from WordPress.
 * Version:           0.0.1
 * Author:            Nick Braica
 * Author URI:        https://www.braican.com
 * Text Domain:       netlify-deployer
 * License:           MIT License
 * License URI:       http://opensource.org/licenses/MIT
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/braican/netlify-deployer
 */

namespace NetlifyDeployer;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*
 * CONSTANTS
 */

// Define the plugin version.
define( 'NETLIFY_DEPLOYER_VERSION', '0.0.1' );

// Plugin directory uri.
define( 'NETLIFY_DEPLOYER_DIRECTORY_URI', plugin_dir_url( __FILE__ ) );


/*
 * LOADER
 */

// Load the autoloader.
include_once plugin_dir_path( __FILE__ ) . 'lib/autoloader.php';


/**
 * The main class to run the plugin.
 */
class NetlifyDeployer {
	/**
	 * The unique instance of this class.
	 *
	 * @var NetlifyDeployer\NetlifyDeployer
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
	 * @return NetlifyDeployer\NetlifyDeployer
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Define core functionality of the plugin. Set some identifying information, load
	 *  dependencies, and set hooks.
	 */
	private function __construct() {
		if ( defined( 'NETLIFY_DEPLOYER_VERSION' ) ) {
			$this->version = NETLIFY_DEPLOYER_VERSION;
		} else {
			$this->version = '0.0.1';
		}

		$this->plugin_name = 'netlify-deployer';

		$admin    = Admin::get_instance();
		$deployer = Deployer::get_instance();
	}
}

/**
 * Initialize
 */
function init() {
	$netlify_deployer = NetlifyDeployer::get_instance();
}
add_action( 'plugins_loaded', 'NetlifyDeployer\init' );
