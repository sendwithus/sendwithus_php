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
    private $SEGMENT_ID = 'seg_VC8FDxDno9X64iUPDFSd76';

    private $options = null;

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

        $this->inline = 'test/test_img.png';

        $this->files = array('test/test_img.png', 'test/test_txt.txt');

        $this->tags = array('tag_one', 'tag_two');

        $this->template_id = 'pmaBsiatWCuptZmojWESme';

        $this->version_id = 'ver_pYj27c8DTBsWB4MRsoB2MF';

        $this->enabled_drip_campaign_id = 'dc_Rmd7y5oUJ3tn86sPJ8ESCk';

        $this->enabled_drip_campaign_step_id = 'dcs_yaAMiZNWCLAEGw7GLjBuGY';

        $this->disabled_drip_campaign_id = 'dc_AjR6Ue9PHPFYmEu2gd8x5V';

        $this->false_drip_campaign_id = 'false_drip_campaign_id';

        $this->log_id = '130be975-dc07-4071-9333-58530e5df052-i03a5q';

        $this->group_id = 'grp_NrSQ5sJdCGpBRLkqiTGVN4';

        $this->api->create_customer(
            $this->recipient['address'],
            array("data" => $this->data)
        );

        $this->group_name = 'test_group';

        $this->group_description = 'test';

        $this->group_update_description = 'testtest';

        $this->bad_group_id = 'notanid';
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

    private function getGroupId($name){
        $r = $this->api->list_groups();
        $data = $r->groups;
        foreach ($data as $group){
            if ($group->name == $name){
              return $group->id;  
            }
            
        }
        return null;
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

    public function testAddCustomerToGroup() {
        $r = $this->api->add_customer_to_group('person@example.com', $this->group_id);
        $this->assertSuccess($r);

        print 'Test add to group';
    }

    public function testRemoveCustomerFromGroup() {
        $r = $this->api->add_customer_to_group('person@example.com', $this->group_id);
        $this->assertSuccess($r);

        print 'Test remove from group';
    }

    public function testListGroups() {
        $r = $this->api->list_groups();
        $this->assertSuccess($r);

        print 'Test list groups';
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

    public function testGetSegments() {
        $r = $this->api->get_segments($this->EMAIL_ID, $this->SEGMENT_ID);
        $this->assertNotNull($r);

        print 'Test get segments';
    }

    public function testSendSegment() {
        $r = $this->api->send_segment($this->EMAIL_ID, $this->SEGMENT_ID);
        $this->assertSuccess($r);

        print 'Test send segment';
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

    public function testListCustomersOnCampaign(){
        $r = $this->api->list_drip_campaign_customers($this->enabled_drip_campaign_id);

        $this->assertEquals($r->id, $this->enabled_drip_campaign_id);

        print 'Test list customers on drip campaign';
    }

    public function testListCustomersOnCampaignStep(){
        $r = $this->api->list_drip_campaign_step_customers($this->enabled_drip_campaign_id, $this->enabled_drip_campaign_step_id);

        $this->assertEquals($r->id, $this->enabled_drip_campaign_step_id);

        print 'Test list customers on a drip campaign step';
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
    

    public function testCreateGroup(){
        $r = $this->api->create_group($this->group_name, $this->group_description);
        $this->assertSuccess($r);

        print 'Test creating a group';
    }

    public function testCreateGroupAgain(){
        $r = $this->api->create_group($this->group_name, $this->group_description);
        $this->assertFail($r);

        print 'Test creating the same group a second time';
    }
    
    public function testUpdateGroup(){
        $id = $this->getGroupId($this->group_name);
        $r = $this->api->update_group($this->group_name, $id, $this->group_update_description);
        $this->assertSuccess($r);

        print 'Test update group name';
    }

    public function testUpdateBadGroup(){
        $r = $this->api->update_group($this->group_name, $this->bad_group_id, $this->group_update_description);
        $this->assertFail($r);

        print 'Test update non-existant group';
    }

    public function testDeleteGroup(){
        $id = $this->getGroupId($this->group_name);
        $r = $this->api->delete_group($id);
        $this->assertSuccess($r);

        print 'Test deleting a group';
    }

    public function testDeleteBadGroup(){
        $r = $this->api->delete_group($this->bad_group_id);
        $this->assertFail($r);

        print 'Test deleting an already deleted group';
    }

}

?>
