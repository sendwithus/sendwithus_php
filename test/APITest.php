<?php
/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

require_once(dirname(__FILE__) . '/../lib/API.php');
require_once(dirname(__FILE__) . '/../lib/Error.php');
// require_once 'PHPUnit/Autoload.php';

class APITestCase extends PHPUnit_Framework_TestCase
{
    private $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    private $EMAIL_ID = 'test_fixture_1';

    private $options = null;

    /** @var \sendwithus\API  */
    private $api = null;
    private $recipient = null;
    private $incompleteRecepient = null;
    private $sender = null;
    private $data = null;
    private $cc = null;
    private $bcc = null;


    function setUp() {

        $this->options = array(
            'DEBUG' => false
        );

        $this->api = new \sendwithus\API($this->API_KEY, $this->options);

        $this->good_html = '<html><head></head><body></body></html>';

        $this->bad_html = '<html><hed><body></body</html>';

        $this->bad_email = 'flerp@asuih';

        $this->log_address = 'person@example.com';

        $this->recipient = array(
            'name' => 'Unit Tests - PHP Client',
            'address' => 'swunit+phpclient@sendwithus.com');

        $this->incompleteRecipient = array(
            'name' => 'Unit Tests - PHP Client');

        $this->sender = array(
            'name' => 'Company Name',
            'address' => 'company@example.com',
            'reply_to' => 'info@example.com');

        $this->data = array(
            'name' => 'Jimmy the snake');

        $this->cc = array(
            array(
                'name' => 'test cc',
                'address' => 'testcc@example.com'
            )
        );

        $this->bcc = array(
            array(
                'name' => 'test bcc',
                'address' => 'testbcc@example.com'
            )
        );

        $this->inline = __DIR__ . '/test_img.png';

        $this->files = array(__DIR__ . '/test_img.png', __DIR__ . '/test_txt.txt');

        $this->tags = array('tag_one', 'tag_two');

        $this->template_id = 'pmaBsiatWCuptZmojWESme';

        $this->version_id = 'ver_pYj27c8DTBsWB4MRsoB2MF';

        $this->enabled_drip_campaign_id = 'dc_Rmd7y5oUJ3tn86sPJ8ESCk';

        $this->enabled_drip_campaign_step_id = 'dcs_yaAMiZNWCLAEGw7GLjBuGY';

        $this->disabled_drip_campaign_id = 'dc_AjR6Ue9PHPFYmEu2gd8x5V';

        $this->false_drip_campaign_id = 'false_drip_campaign_id';

        $this->api->create_customer(
            $this->recipient['address'],
            array("data" => $this->data)
        );

        $send = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => $this->data)
        );

        $this->log_id = $send->receipt_id;
    }

    function tearDown() {
        $this->api->create_customer(
            $this->recipient['address'],
            array("data" => $this->data)
        );
    }

    private function assertSuccess($r) {
        $this->assertEquals($r->status, "OK");
        $this->assertTrue($r->success);
    }

    private function assertFail($r) {
        $this->assertNotEquals($r->code, 200);
        $this->assertEquals($r->status, "error");
        $this->assertFalse($r->success);
        $this->assertNotNull($r->exception);
    }

    public function testGetEmails() {
        $r = $this->api->emails();
        $this->assertNotNull($r);
        print 'Got emails';
    }

    public function testGetLogs() {
        $r = $this->api->logs();
        $this->assertNotNull($r);
        print 'Got logs';
    }

    public function testGetLogsWithTimestamp() {
        $r = $this->api->logs($created_lt=time());
        $this->assertNotNull($r);
        print 'Got time stamped logs';
    }

    public function testGetSingleLog() {
        $r = $this->api->get_log($this->log_id);
        $this->assertNotNull($r);
        print 'Getting a log';
    }

    public function testCreateEmailSuccess() {
        $r = $this->api->create_email(
            'test name',
            'test subject',
            $this->good_html
        );

        $this->assertNotNull($r);
        print 'Created an email';
    }

    public function testCreateNewTemplateVersion(){
        $r = $this->api->create_new_template_version(
            'test name',
            'test subject',
            $this->template_id,
            $html=$this->good_html
        );
        $this->assertNotNull($r->created);
        print "Created a new template version";
    }

    public function testUpdateTemplateVersion(){
        $r = $this->api->update_template_version(
            'test name',
            'test subject',
            $this->template_id,
            $this->version_id,
            $this->good_html
        );
        $this->assertNotNull($r->created);
        print "Updated a template version";
    }

    public function testSimpleSend() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => $this->data)
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send';
    }

    public function testSendWithEmptyData() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => array())
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Send with empty data';
    }

    public function testSendWithNullData() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => null)
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Send with null data';
    }

    public function testSendWithSender() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "sender" => $this->sender
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with Sender';
    }

    public function testSendWithCC() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "cc" => $this->cc
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with CC';
    }

    public function testSendWithBCC() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "bcc" => $this->bcc
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with bcc';
    }

    public function testSendWithInline() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "inline" => $this->inline
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with inline';
    }

    public function testSendWithInlineEncoded() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "inline" => array(
                    'id' => basename($this->files[0]),
                    'data' => base64_encode(file_get_contents($this->files[0]))
                )
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with inline';
    }

    public function testSendWithFiles() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "files" => $this->files
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with file attachments';
    }

    public function testSendWithFilesEncoded() {

        $files = array(
            array(
                'id' => basename($this->files[0]),
                'data' => base64_encode(file_get_contents($this->files[0]))
            ),
            array(
                'id' => basename($this->files[1]),
                'data' => base64_encode(file_get_contents($this->files[1]))
            )
        );

        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "files" => $files
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with file attachments';
    }

    public function testSendWithTags() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array(
                "data" => $this->data,
                "tags" => $this->tags
            )
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->receipt_id);
        print 'Simple send with tags';
    }

    public function testSendIncomplete() {
        $r = $this->api->send(
            $this->EMAIL_ID,
            $this->incompleteRecipient,
            array(
                "data" => $this->data,
                "sender" => $this->sender
            )
        );

        $this->assertFail($r);
        $this->assertEquals($r->code, 400); // incomplete

        print 'Simple bad send';
    }

    public function testResend(){
        sleep(10);
        // $send = $this->api->send(
        //     $this->EMAIL_ID,
        //     $this->recipient,
        //     array("data" => $this->data)
        // );

        // $r = $this->api->resend($send->receipt_id);
        $r = $this->api->resend($this->log_id);
        $this->assertSuccess($r);

        print 'Test resend mail from log';
    }

    public function testResendFailed(){
        $r = $this->api->resend('i-do-not-exist-log-id');
        $this->assertFail($r);

        print 'Test resend mail with invalid log';
    }

    public function testInvalidAPIKey() {
        $api = new \sendwithus\API('INVALID_API_KEY', $this->options);

        $r = $api->send(
            $this->EMAIL_ID,
            $this->recipient,
            array("data" => $this->data)
        );

        $this->assertFail($r);
        $this->assertEquals($r->code, 403); // bad api key

        print 'Test bad api key';
    }


    public function testInvalidEmailId() {
        $r = $this->api->send(
            'INVALID_EMAIL_ID',
            $this->recipient,
            array("data" => $this->data)
        );

        $this->assertFail($r);
        $this->assertEquals($r->code, 400); // email_id not found

        print 'Test invalid email id';
    }

    public function testRender() {
        $r = $this->api->render(
            $this->EMAIL_ID,
            array("data" => $this->data)
        );

        $this->assertSuccess($r);
        $this->assertNotNull($r->html ?: $r->text);
        print 'Test render';
    }

    public function testCreateCustomer() {
        $r = $this->api->create_customer(
            $this->recipient['address'],
            array("data" => $this->data)
        );

        $this->assertSuccess($r);
        print 'Test create customer';
    }

    public function testGetCustomer() {
        $r = $this->api->create_customer(
            $this->recipient['address'],
            array("data" => $this->data)
        );
        $this->assertSuccess($r);
        $r = $this->api->get_customer(
            $this->recipient['address']
        );
        $this->assertSuccess($r);

        print 'Test get customer';
    }

    public function testUpdateCustomer() {
        $r = $this->api->update_customer(
            $this->recipient['address'],
            array("data" => $this->data)
        );

        $this->assertSuccess($r);
        print 'Test update customer';
    }

    public function testDeleteCustomer() {
        $r = $this->api->create_customer($this->recipient['address']);
        $this->assertSuccess($r);

        $r = $this->api->delete_customer($this->recipient['address']);
        $this->assertSuccess($r);

        print 'Test delete customer';
    }

    public function testCustomerConversion() {
        $r = $this->api->customer_conversion($this->recipient['address']);
        $this->assertSuccess($r);

        print 'Test customer conversion';
    }

    public function testCustomerConversionRevenue() {
        $r = $this->api->customer_conversion($this->recipient['address'], 1234);
        $this->assertSuccess($r);

        print 'Test customer conversion revenue';
    }

    public function testListDripCampaigns(){
        $r = $this->api->list_drip_campaigns();
        $this->assertNotNull($r);

        print 'Test list drip campaigns';
    }

    public function testListDripCampaignDetails(){
        $r = $this->api->drip_campaign_details($this->enabled_drip_campaign_id);

        $this->assertEquals($r->name, 'TEST_CAMPAIGN');

        print 'Test list drip campaign details';
    }

    public function testStartOnEnabledDripCampaign(){
        $r = $this->api->drip_campaign_details($this->enabled_drip_campaign_id);
        $this->assertTrue($r->enabled);

        $r = $this->api->start_on_drip_campaign('person@example.com',$this->enabled_drip_campaign_id);
        $this->assertSuccess($r);

        print 'Test add to enabled drip campaigns';
    }

    public function testStartOnEnabledDripCampaignWithData(){
        $r = $this->api->drip_campaign_details($this->enabled_drip_campaign_id);
        $this->assertTrue($r->enabled);

        $r = $this->api->start_on_drip_campaign('person@example.com',$this->enabled_drip_campaign_id, $this->data);
        $this->assertSuccess($r);

        print 'Test add to enabled drip campaigns with data';
    }

    public function testStartOnDisabledDripCampaign(){
        $r = $this->api->drip_campaign_details($this->disabled_drip_campaign_id);
        $this->assertFalse($r->enabled);

        $r = $this->api->start_on_drip_campaign('person@example.com',$this->disabled_drip_campaign_id);
        $this->assertFail($r);

        print 'Test add to disabled drip campaigns';
    }

    public function testStartOnFalseDripCampaign(){
        $r = $this->api->drip_campaign_details($this->false_drip_campaign_id);
        $this->assertFail($r);

        $r = $this->api->start_on_drip_campaign('person@example.com',$this->false_drip_campaign_id);
        $this->assertFail($r);

        print 'Test add to false drip campaigns';
    }

    public function testRemoveOnDripCampaign(){
        $r = $this->api->remove_from_drip_campaign('person@example.com',$this->enabled_drip_campaign_id);
        $this->assertSuccess($r);

        print 'Test remove from drip campaigns';
    }

    public function testListDripCampaignSteps(){
        $r = $this->api->drip_campaign_details($this->enabled_drip_campaign_id);

        $this->assertEquals($r->name, 'TEST_CAMPAIGN');
        print 'Test list drip campaign steps';
    }


    public function testGetCustomerLogs(){
        $logs = $this->api->get_customer_logs($this->log_address);
        $this->assertEquals(false, empty($logs->logs));

        print 'Test retrieving real customer logs';
    }

    public function testGetBadCustomerLogs(){
        $logs = $this->api->get_customer_logs($this->bad_email);
        $this->assertEquals(True, empty($logs->logs));

        print 'Test retrieving non-existant customer logs';
    }

    public function testBatchConstructor() {
        $batch = new \sendwithus\BatchAPI($this->API_KEY, $this->options);
        $this->assertNotEmpty($batch);
    }

    public function testBatchApiRequest() {
        $batch = $this->api->start_batch();
        $result = $batch->get_customer_logs($this->log_address);
        $this->assertTrue($result->success);
        $this->assertEquals('Batched', $result->status);
        $result = $batch->execute();
        $this->assertTrue(is_array($result));
    }

    public function testBatchCreateCustomer() {
       $batch_api_one = $this->api->start_batch();
       $batch_api_two = $this->api->start_batch();

       $data = array('segment' => 'Batch Updated Customer');
       for($i = 0; $i < 10; $i++) {
           $result = $batch_api_one->create_customer(sprintf('test+php+%s@sendwithus.com', $i), $data);
           $this->assertTrue($result->success);
           $this->assertEquals('Batched', $result->status);
           $this->assertEquals($batch_api_one->command_length(), $i + 1);

           if ($i % 2 == 0) {
               $batch_api_two->create_customer(sprintf('test+php+%s+again@sendwithus.com', $i), $data);
               $this->assertEquals($batch_api_two->command_length(), ($i/2) + 1);
           }
       }

       // Run batch 1
       $result = $batch_api_one->execute();
       $this->assertEquals(count($result), 10);
       foreach($result as $response) {
           $this->assertEquals($response->status_code, 200);
       }

       // Batch one should be empty, batch two still full
       $this->assertEquals($batch_api_one->command_length(), 0);
       $this->assertEquals($batch_api_two->command_length(), 5);

       // Run batch 2
       $result = $batch_api_two->execute();
       $this->assertEquals(count($result), 5);
       foreach($result as $response) {
           $this->assertEquals($response->status_code, 200);
       }

       // Batch one should be empty, batch two still full
       $this->assertEquals($batch_api_one->command_length(), 0);
       $this->assertEquals($batch_api_two->command_length(), 0);

       print 'Test creating customers in batch';
    }

    public function testBatchCancel() {
        $batch_api = $this->api->start_batch();

        $data = array('segment' => 'Batch Updated Customer');
        for($i = 1; $i <= 10; $i++) {
            $result = $batch_api->create_customer(sprintf('test+php+%s@sendwithus.com', $i), $data);
            $this->assertTrue($result->success);
            $this->assertEquals('Batched', $result->status);
            $this->assertEquals($i, $batch_api->command_length());
        }

        $result = $batch_api->cancel();
        $this->assertTrue($result->success);
        $this->assertEquals('Canceled', $result->status);
        $this->assertEquals(0, $batch_api->command_length());
    }

}
?>
