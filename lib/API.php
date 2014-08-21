<?php

namespace sendwithus;

require 'Error.php';

/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

class API {
    private $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    private $API_HOST = 'api.sendwithus.com';
    private $API_PORT = '443';
    private $API_PROTO = 'https';
    private $API_VERSION = '1';
    private $API_HEADER_KEY = 'X-SWU-API-KEY';
    private $API_HEADER_CLIENT = 'X-SWU-API-CLIENT';
    private $API_CLIENT_VERSION = "2.2.0";
    private $API_CLIENT_STUB = "php-%s";

    private $DEBUG = false;

    public function __construct($api_key, $options = array()) {
        $this->API_KEY = $api_key;
        $this->API_CLIENT_STUB = sprintf($this->API_CLIENT_STUB,
            $this->API_CLIENT_VERSION);

        foreach ($options as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Send an email
     *
     * The additional optional parameters are as follows:
     *     'email_data' - Default is null. Array of variables to merge into the template.
     *     'sender' - Default is null. Array ("address", "name", "reply_to") of sender.
     *     'cc' - Default is null. Array of ("address", "name") for carbon copy.
     *     'bcc' - Default is null. Array of ("address", "name") for blind carbon copy.
     *     'inline' - Default is null. String, path to file to include inline.
     *     'tags' - Default is null. Array of strings to tag email send with.
     *     'version_name' - Default is blank. String, name of version to send
     *
     * @param string $email_id ID of email to send
     * @param array $recipient array of ("address", "name") to send to
     * @param array $args (optional) additional optional parameters
     * @return array API response object
     */
    public function send($email_id, $recipient, $args = null) {
        $endpoint = "send";

        $payload = array(
            "email_id" => $email_id,
            "recipient" => $recipient
        );

        if (is_array($args)) {
            $payload = array_merge($args, $payload);
        }

        // Optional inline attachment
        if (isset($payload['inline'])) {
            $inline_attachment_path = $payload['inline'];

            $payload["inline"] = array(
                "id" => basename($inline_attachment_path),
                "data" => $this->encode_attachment($inline_attachment_path)
            );
        }

        // Optional file attachment
        if (isset($payload['files'])) {
            foreach ($payload['files'] as &$file) {
              $file = array(
                  "id" => basename($file),
                  "data" => $this->encode_attachment($file)
              );
            }
        }

        if ($this->DEBUG) {
            error_log(sprintf("sending email `%s` to \n", $email_id));
            error_log(print_r($recipient, true));
            if (isset($payload['sender'])) {
                error_log(sprintf("\nfrom\n"));
                error_log(print_r($payload['sender'], true));
            }
            error_log(sprintf("\nwith\n"));
            error_log(print_r($payload, true));
        }

        return $this->api_request($endpoint, $payload);
    }

    /**
     * Get Emails
     *
     * @return array API response object.
     */
    public function emails() {
        $endpoint = "templates";
        $payload = NULL;
        return $this->api_request($endpoint, $payload, null, "GET");
    }

    /**
     * Get a specific template
     * 
     * @param string $template_id template id
     * @param string $version_id optional version id to get template version
     *
     * @return array API response object
     */
    public function get_template($template_id, $version_id = null){
        $endpoint = "templates/" . $template_id;
        
        if($version_id){
            $endpoint .= "/versions/" . $version_id;
        }
        
        $payload = NULL;
        return $this->api_request($endpoint, $payload, null, "GET");
    }

    /**
     * Send to a Segment
     *
     * @param string $email_id template id
     * @param string $segment_id segment to send to
     * @param array $data dynamic data for send
     *
     * @return array API response object.
     */
    public function send_segment($email_id, $segment_id, $data = null) {
        $endpoint = 'segments/' . $segment_id . '/send';
        $payload = array("email_id" => $email_id);

        if (is_array($data)) {
            $payload['email_data'] = $data;
        }

        return $this->api_request($endpoint, $payload);
    }

    /**
     * Create Customer
     *
     * @param string $email customer email
     * @param array $data customer data to
     *
     * @return array API response object.
     */
    public function create_customer($email, $data=null) {
        $endpoint = "customers";
        $payload = array("email" => $email);

        if (is_array($data)) {
            $payload['data'] = $data;
        }

        return $this->api_request($endpoint, $payload);
    }

    /**
     * Update Customer
     *
     * @param string $email customer email
     * @param array $data customer data to
     *
     * @return array API response object.
     */
    public function update_customer($email, $data=null) {
        return $this->create_customer($email, $data);
    }

    /**
     * Delete Customer
     *
     * @param string $email customer email
     * @param array $data customer data to
     *
     * @return array API response object.
     */
    public function delete_customer($email) {
        $endpoint = "customers/" . $email;
        $payload = NULL;
        return $this->api_request($endpoint, $payload, null, "DELETE");
    }

    /**
     * Create an Email
     *
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @return array API response object
     */
    public function create_email($name, $subject, $html, $text=null) {
        $endpoint = "templates";

        $payload = array(
            "name" => $name,
            "subject" => $subject,
            "html" => $html
        );

        // set optional text
        if ($text) {
            $payload["text"] = $text;
        }

        if ($this->DEBUG) {
            error_log(sprintf("creating email with name %s and subject %s\n", $name, $subject));
        }

        return $this->api_request($endpoint, $payload);
    }

    /**
     * Create new template version
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $template_id template id
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @return array API response object
     */
    public function create_new_template_version($name, $subject, $template_id, $html, $text=null) {
        $endpoint = "templates/" . $template_id . "/versions";

        $payload = array(
            "name" => $name,
            "subject" => $subject,
            "html" => $html
        );

        // set optional text
        if ($text) {
            $payload["text"] = $text;
        }

        if ($this->DEBUG) {
            error_log(sprintf("creating a new template version with name %s and subject %s\n", $name, $subject));
        }

        return $this->api_request($endpoint, $payload);
    }

    /**
     * Update template version
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $template_id template id
     * @param string $version_id template version id
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @return array API response object
     */
    public function update_template_version($name, $subject, $template_id, $version_id, $html, $text=null) {
        $endpoint = "templates/" . $template_id . "/versions/" . $version_id;

        $payload = array(
            "name" => $name,
            "subject" => $subject,
            "html" => $html
        );

        // set optional text
        if ($text) {
            $payload["text"] = $text;
        }

        if ($this->DEBUG) {
            error_log(sprintf("updating template\n ID:%s\nVERSION:%s\n with name %s and subject %s\n", $template_id, $version_id, $name, $subject));
        }

        return $this->api_request($endpoint, $payload, null, "PUT");
    }

    /**
     * Get Email Send Logs
     *
     * @param string $count (optional) the number of logs to return. Max: 100
     * @param array $offset (optional) offset the number of logs to return
     * @return array API response object
     */
    public function logs($count = 100, $offset = 0) {
        $endpoint = "logs";

        $params = array(
            "count" => $count,
            "offset" => $offset
        );

        return $this->api_request($endpoint, null, $params, "GET");
    }

    /**
     * @deprecated deprecated since v2.1.0 use the new drips
     * Unsubscribe email address from active drips
     *
     * @param string $email_address the email to unsubscribe from active drips
     * @return array API response object
     */
    public function drip_unsubscribe($email_address) {
        $endpoint = "drips/unsubscribe";

        $payload = array(
            "email_address" => $email_address
        );
        
        if ($this->DEBUG) {
            error_log(sprintf("WARNING: Old campaign endpoints will soon be deprecated.")); // Is that good enough?
        }

        return $this->api_request($endpoint, $payload);
    }

    /**
     * List drip campaigns
     *
     * @return array API response object
     */
    public function list_drip_campaigns(){
        $endpoint = "drip_campaigns";
        return $this->api_request($endpoint, null, null, "GET");
    }

    /**
     * Start on drip campaign
     *
     * @param string $recipient_address email address being added to drip campaign
     * @param string $drip_campaign_id drip campaign being added to
     * @return array API response object
     */
    public function start_on_drip_campaign($recipient_address, $drip_campaign_id){
        $endpoint = "drip_campaigns/" . $drip_campaign_id . "/activate";
        
        $payload = array(
            "recipient" => $recipient_address
        );
        
        return $this->api_request($endpoint, $payload);
    }    

    /**
     * Remove from drip campaign
     *
     * @param string $recipient_address email address being added to drip campaign
     * @param string $drip_campaign_id drip campaign being added to
     * @return array API response object
     */
    public function remove_on_drip_campaign($recipient_address, $drip_campaign_id){
        $endpoint = "drip_campaigns/" . $drip_campaign_id . "/deactivate";
        
        $payload = array(
            "recipient" => $recipient_address
        );
        
        return $this->api_request($endpoint, $payload);
    }

    /**
     * List drip campaign steps
     *
     * @param string $drip_campaign_id id of drip campaign
     * @return array API response object
     */
    public function list_drip_campaign_steps($drip_campaign_id){
        $endpoint = "drip_campaigns/" . $drip_campaign_id . "/steps";

        return $this->api_request($endpoint, null, null, "GET");
    }
   
    /**
     * Render an email template with the provided data
     *
     * The additional optional parameters are as follows:
     *     'template_data' - Default is null. Array of variables to merge into the template.
     *
     * @param string $email_id ID of email to send
     * @param array $args (optional) additional optional parameters
     * @return array API response object
     */
    public function render($email_id, $args = null) {
        $endpoint = "render";

        $payload = array(
            "template_id" => $email_id
        );

        if (is_array($args)) {
            $payload = array_merge($args, $payload);
        }

        if ($this->DEBUG) {
            error_log(sprintf("rendering template `%s` with \n", $email_id));
            error_log(print_r($payload, true));
        }

        return $this->api_request($endpoint, $payload);
    }

    /**
     * Helper function to Base64 encode files and return the encoded data
     *
     * @param string $path Local path of the file to encode
     * @return string/false the encoded file data or false on failure
     */
    private function encode_attachment($path) {
      if (!is_string($path)) {
          $e = sprintf("inline parameter must be path to file as string, received: %s", gettype($path));
          throw new API_Error($e);
      }

      $file_data = file_get_contents($path);

      return base64_encode($file_data);
    }

    private function build_path($endpoint) {
        $path = sprintf("%s://%s:%s/api/v%s/%s",
            $this->API_PROTO,
            $this->API_HOST,
            $this->API_PORT,
            $this->API_VERSION,
            $endpoint);

        return $path;
    }

    private function api_request($endpoint, $payload, $params = null, $request = "POST") {
        $path = $this->build_path($endpoint);
        $response = array();

        if ($params)
        {
            $path = $path . '?' . http_build_query($params);
        }

        $ch = curl_init($path);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);

        // set payload
        $payload_string = null;
        if ($payload) {
            $payload_string = json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_string);
        }

        // set headers
        if ($payload && ($request == "POST" || $request == "PUT")) {
            $httpheaders = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload_string),
                $this->API_HEADER_KEY . ": " . $this->API_KEY,
                $this->API_HEADER_CLIENT . ": " . $this->API_CLIENT_STUB
                );
        }
        else {
            $httpheaders = array(
                'Content-Type: application/json',
                $this->API_HEADER_KEY . ": " . $this->API_KEY,
                $this->API_HEADER_CLIENT . ": " . $this->API_CLIENT_STUB
                );
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/data/ca-certificates.pem');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);

        if ($this->DEBUG) {
            // enable curl verbose output to STDERR
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            error_log(sprintf("payload: %s\r\n", $payload_string));
            error_log(sprintf("path: %s\r\n", $path));
        }

        try {
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = json_decode( $result );

            if ($code != 200) {
                throw new API_Error("Request was not successful", $code, $result, $response);
            }
        } catch (API_Error $e) {
            if ($this->DEBUG) {
                error_log(sprintf("Caught exception: %s\r\n", $e->getMessage()));
                error_log(print_r($e, true));
            }

            $response = (object) array(
                'code' => $code,
                'status' => "error",
                'success' => false,
                'exception' => $e
                );
        }

        curl_close($ch);

        return $response;
    }
}

?>
