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

class Deployer {
    /**
     * The unique instance of the Deployer class.
     * 
     * @var NetlifyDeployer\Deployer
     */
    private static $instance;

    /**
     * Gets the instance of the class.
     * 
     * @return NetlifyDeployer\Deployer
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
        $this->add_ajax_actions();
    }


    /**
     * Add the ajax actions.
     * 
     * @return void
     */
    private function add_ajax_actions() {
        add_action('wp_ajax_trigger_deploy', array($this, 'trigger_deploy'));
    }


    /**
     * Trigger a deploy.
     * 
     * @return void
     */
    public function trigger_deploy() {
        if (!isset($_POST['build_hook'])) {
            wp_send_json_error('No build hook set.');
        }

        $build_hook = $_POST['build_hook'];
        $respnse = $this->post($build_hook);

        // If the POST request from Netlify isn't empty, there is probably an error.
        if (!empty($response)) {
            wp_send_json_error($response);
        }

        // Reset the changes counter.
        $admin = Admin::get_instance();
        $options = get_option($admin->option_group);
        $options['changes'] = 0;
        update_option($admin->option_group, $options);

        wp_send_json_success('Deployment successfully triggered.');
    }


    /**
     * CURL POST request.
     * 
     * @param string  $url  URL to post to.
     * 
     * @return mixed Response from the url.
     */
    private function post($url) {
        $data = '{}';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Length: ' . strlen($data))
        );
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}