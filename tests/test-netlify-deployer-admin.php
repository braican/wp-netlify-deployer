<?php
/**
 * Netlify Deployer Tests
 * 
 * @package NetlifyDeployer
 */

class NetlifyDeployer_Admin_Test extends \WP_UnitTestCase {
  public function setup() {
    parent::setup();

    $this->class_instance = NetlifyDeployer\NetlifyDeployer::get_instance();
    $this->admin = NetlifyDeployer\Admin::get_instance();
  }

  public function test_admin_keys() {
    $this->assertEquals('deployer_settings', $this->admin->option_group);
  }

  public function test_increment_save_on_creation_no_previous_undeployed_changes() {
    update_option($this->admin->option_group, array(
      'build_hook_url' => 'https://google.com'
    ));

    wp_insert_post(array(
      'post_title' => 'Test post'
    ));

    $options = get_option($this->admin->option_group);

    $this->assertEquals(array(
      'build_hook_url' => 'https://google.com',
      'undeployed_changes' => 1
    ), $options);
  }

  public function test_increment_save_on_creation_existing_undeployed_changes() {
    update_option($this->admin->option_group, array(
      'build_hook_url' => 'https://google.com',
      'undeployed_changes' => 12
    ));

    wp_insert_post(array(
      'post_title' => 'Test post'
    ));

    $options = get_option($this->admin->option_group);

    $this->assertEquals(array(
      'build_hook_url' => 'https://google.com',
      'undeployed_changes' => 13
    ), $options);
  }

  public function test_increment_save_on_edit_no_previous_undeployed_changes() {
    update_option($this->admin->option_group, array(
      'build_hook_url' => 'https://google.com'
    ));

    $post_id = wp_insert_post(array(
      'post_title' => 'Test post'
    ));

    wp_update_post(array(
      'ID' => $post_id,
      'post_title' => 'Test post - modified'
    ));

    $options = get_option($this->admin->option_group);

    $this->assertEquals(array(
      'build_hook_url' => 'https://google.com',
      'undeployed_changes' => 2
    ), $options);
  }

  public function test_increment_save_on_edit_existing_undeployed_changes() {
    update_option($this->admin->option_group, array(
      'build_hook_url' => 'https://google.com',
      'undeployed_changes' => 49
    ));

    $post_id = wp_insert_post(array(
      'post_title' => 'Test post'
    ));

    wp_update_post(array(
      'ID' => $post_id,
      'post_title' => 'Test post - modified'
    ));

    $options = get_option($this->admin->option_group);

    $this->assertEquals(array(
      'build_hook_url' => 'https://google.com',
      'undeployed_changes' => 51
    ), $options);
  }
}