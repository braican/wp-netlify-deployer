<?php
/**
 * Netlify Deployer Tests
 *
 * @package   NetlifyDeployer
 * @author    Nick Braica
 * @license   MIT License
 * @link      https://www.braican.com
 * @copyright 2019 Nick Braica
 */
class NetlifyDeployer_Deployer_Test extends WP_Ajax_UnitTestCase {
	public function setup() {
		parent::setup();

		$this->admin = NetlifyDeployer\Admin::get_instance();
	}

	public function test_build_hook_not_set() {
		try {
				$this->_handleAjax( 'trigger_deploy' );
		} catch ( WPAjaxDieContinueException $e ) {
				// We expected this, do nothing.
		}

		// Check that the exception was thrown.
		$response = json_decode($this->_last_response );
		$this->assertTrue( isset( $e ) );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertFalse($response->success);
		$this->assertEquals('No build hook set.', $response->data);
	}

	public function test_build_hook_set() {
		$_POST['build_hook'] = 'empty';

		try {
			$this->_handleAjax('trigger_deploy');
		} catch (WPAjaxDieContinueException $e) {
			// We expect this
		}

		$response = json_decode($this->_last_response);
		$this->assertTrue( isset( $e ) );
		$this->assertInternalType( 'object', $response );
		$this->assertObjectHasAttribute( 'success', $response );
		$this->assertTrue($response->success);
		$this->assertEquals('Deployment successfully triggered.', $response->data);

		// Assert that the `undeployed_changes` setting was reset.
		$options = get_option($this->admin->webhook_group);
		$this->assertEquals(0, $options['undeployed_changes']);

	}
}
