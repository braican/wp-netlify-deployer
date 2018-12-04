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

class Deployer {
    /**
     * The unique instance of the Deployer class.
     * 
     * @var NetlifyDeploybot\Deployer
     */
    private static $instance;

    /**
     * Gets the instance of the class.
     * 
     * @return NetlifyDeploybot\Deployer
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

        // $respnse = $this->post($build_hook);

        // If the POST request from Netlify isn't empty, there is probably an error.
        // if (!empty($response)) {
        //     wp_send_json_error($response);
        // }

        $admin = Admin::get_instance();
        $options = get_option($admin->option_group);
        error_log(print_r($options, true));

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