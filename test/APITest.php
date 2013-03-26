<?php
/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

require 'lib/API.php';

$simpletest = @include_once(dirname(__FILE__).'/simpletest/autorun.php');
if (!$simpletest) {
  echo "Missing Dependancy: The API test cases depend on SimpleTest. ".
       "Download and install it in your PHP include_path or put it in the test/ directory.\n";
  exit(1);
}


class APITestCase extends UnitTestCase
{
	private $API_KEY = 'THIS_IS_A_TEST_API_KEY';
	private $EMAIL_ID = 'test_fixture_1';

	private $options = null;

	private $api = null;
	private $recipient = null;
	private $incompleteRecepient = null;
	private $sender = null;
	private $data = null;

	function setUp() {

		$this->options = array(
		    'DEBUG' => false
		);

		$this->api = new \sendwithus\API($this->API_KEY, $this->options);

		$this->recipient = array(
			'name' => 'Matt',
			'address' => 'us@sendwithus.com');

		$this->incompleteRecipient = array(
			'name' => 'Matt');

		$this->sender = array(
			'name' => 'Company',
			'address' => 'company@company.com',
			'reply_to' => 'info@company.com');

		$this->data = array(
			'name' => 'Jimmy the snake');
	}

	function tearDown() {

	}

	private function assertSuccess($r) {

		$this->assertNotNull($r->receipt_id);
		$this->assertEqual($r->status, "OK");
		$this->assertTrue($r->success);
	}

	private function assertFail($r) {

		$this->assertNotEqual($r->code, 200);
		$this->assertEqual($r->status, "error");
		$this->assertFalse($r->success);
		$this->assertNotNull($r->exception);
	}

	public function testGetEmails() {

		$r = $this->api->emails();
		$this->assertNotNull($r);
		// print_r($r);
	}

	public function testSimpleSend() {

		$r = $this->api->send(
			$this->EMAIL_ID,
			$this->recipient,
			$this->data);

		$this->assertSuccess($r);
	}

	public function testSendWithSender() {
		
		$r = $this->api->send(
			$this->EMAIL_ID,
			$this->recipient,
			$this->data, 
			$this->sender);

		$this->assertSuccess($r);
	}

	public function testSendIncomplete() {

		$r = $this->api->send(
			$this->EMAIL_ID,
			$this->incompleteRecipient,
			$this->data,
			$this->sender);

		$this->assertFail($r);
		$this->assertEqual($r->code, 400); // incomplete
	}

	public function testInvalidAPIKey() {
		
		$api = new \sendwithus\API('INVALID_API_KEY', $this->options);
		
		$r = $api->send(
			$this->EMAIL_ID,
			$this->recipient,
			$this->data);
		
		$this->assertFail($r);
		$this->assertEqual($r->code, 403); // bad api key
	}


	public function testInvalidEmailId() {

		$r = $this->api->send(
			'INVALID_EMAIL_ID',
			$this->recipient,
			$this->data);

		$this->assertFail($r);
		$this->assertEqual($r->code, 404); // email_id not found
	}
}

?>
