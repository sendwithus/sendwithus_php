<?php

namespace sendwithus;

require 'Error.php';

/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

class API {
    const HTTP_POST = 'POST';
    const HTTP_GET = 'GET';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';

    const LOG_ERR    = 'ERR';
    const LOG_DEBUG  = 'DEBUG';

    protected $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    protected $API_HOST = 'api.sendwithus.com';
    protected $API_PORT = '443';
    protected $API_PROTO = 'https';
    protected $API_VERSION = '1';
    protected $API_HEADER_KEY = 'X-SWU-API-KEY';
    protected $API_HEADER_CLIENT = 'X-SWU-API-CLIENT';
    protected $API_CLIENT_VERSION = "6.4.0";
    protected $API_CLIENT_STUB = "php-%s";
    protected $API_DEBUG_HANDLER = null;

    protected $DEBUG = false;

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

            if (is_string($payload['inline'])) {

                $inline_attachment_path = $payload['inline'];

                $payload["inline"] = array(
                    "id" => basename($inline_attachment_path),
                    "data" => $this->encode_attachment($inline_attachment_path)
                );
            }
        }

        // Optional file attachment
        if (isset($payload['files'])) {
            foreach ($payload['files'] as &$file) {
              if (is_array($file) && isset($file['id']) && isset($file['data'])) {
                  continue;
              }
              $file = array(
                  "id" => basename($file),
                  "data" => $this->encode_attachment($file)
              );
            }
        }

        if ($this->DEBUG) {
            $message = sprintf(
                "Sending email `%s` to \n `%s`",
                $email_id,
                print_r($recipient, true)
            );
            if (isset($payload['sender'])) {
                $message .= sprintf(
                    "\nfrom\n `%s`",
                    print_r($payload['sender'], true)
                );
            }
            $message .= sprintf("\nwith\n%s", print_r($payload, true));
            $this->log_message($message);
        }

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Resend a specific email by id
     *
     * @param string $log_id log id
     * @return array API response object
     */
    public function resend($log_id){
        $endpoint = "resend";

        $payload = array(
            "log_id" => $log_id
        );

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Get Emails
     *
     * @return array API response object.
     */
    public function emails() {
        $endpoint = "templates";
        return $this->api_request($endpoint, self::HTTP_GET);
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

        return $this->api_request($endpoint, self::HTTP_GET);
    }

    /**
     * Get Customer
     *
     * @param string $email customer email
     *
     * @return array API response object.
     */
    public function get_customer($email) {
        $endpoint = "customers/" . $email;

        return $this->api_request($endpoint, self::HTTP_GET);
    }

	/**
	 * Get Customer Logs
	 *
	 * @param string $email customer email
	 *
	 * @return array API response object.
	 */
	public function get_customer_logs( $email ) {
		$endpoint = "customers/" . $email . "/logs";

		return $this->api_request( $endpoint, self::HTTP_GET );
	}

    /**
     * Create Customer
     *
     * @param string $email customer email
     * @param array $data (optional) customer data to
     * @param array $args (optional) optional arguments
     *
     * The additional optional parameters are as follows:
     *     'locale' - Default is null. String to specify a locale for this customer.
     *
     * @return array API response object.
     */
    public function create_customer($email, $data=null, $args=null) {
        $endpoint = "customers";
        $payload = array("email" => $email);

        if (is_array($data)) {
            $payload['data'] = $data;
        }

        if (is_array($args)) {
            $payload = array_merge($args, $payload);
        }

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
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
     *
     * @return array API response object.
     */
    public function delete_customer($email) {
        $endpoint = "customers/" . $email;
        return $this->api_request($endpoint, self::HTTP_DELETE);
    }

    /**
     * Create an Email
     *
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @param string $preheader Optional preheader for the email template
     * @param string $amp_html Optional AMP version of the email template
     * @return array API response object
     */
    public function create_email($name, $subject, $html, $text=null, $preheader=null, $amp_html=null) {
        $endpoint = "templates";

        $payload = array(
            "name" => $name,
            "subject" => $subject,
            "html" => $html
        );

        // set optionals
        if ($text) {
            $payload["text"] = $text;
        }
        if (!is_null($preheader)) {
            $payload["preheader"] = $preheader;
        }
        if (!is_null($amp_html)) {
            $payload["amp_html"] = $amp_html;
        }

        if ($this->DEBUG) {
            $this->log_message(sprintf("creating email with name %s and subject %s\n", $name, $subject));
        }

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Create new template version
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $template_id template id
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @param string $preheader Optional preheader for the email template
     * @param string $amp_html Optional AMP version of the email template
     * @return array API response object
     */
    public function create_new_template_version($name, $subject, $template_id, $html, $text=null, $preheader=null, $amp_html=null) {
        $endpoint = "templates/" . $template_id . "/versions";

        $payload = array(
            "name" => $name,
            "subject" => $subject,
            "html" => $html
        );

        // set optionals
        if ($text) {
            $payload["text"] = $text;
        }
        if (!is_null($preheader)) {
            $payload["preheader"] = $preheader;
        }
        if (!is_null($amp_html)) {
            $payload["amp_html"] = $amp_html;
        }

        if ($this->DEBUG) {
            $this->log_message(
                sprintf("creating a new template version with name %s and subject %s\n", $name, $subject)
            );
        }

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Update template version
     * @param string $name name of the email template
     * @param string $subject subject line for the email template
     * @param string $template_id template id
     * @param string $version_id template version id
     * @param string $html HTML code for the email template
     * @param string $text Optional text version of the email template
     * @param string $preheader Optional preheader for the email template
     * @param string $amp_html Optional AMP version of the email template
     * @return array API response object
     */
    public function update_template_version($name, $subject, $template_id, $version_id, $html, $text=null, $preheader=null, $amp_html=null) {
        $endpoint = "templates/" . $template_id . "/versions/" . $version_id;

        $payload = array(
            "name" => $name,
            "subject" => $subject,
            "html" => $html
        );

        // set optionals
        if ($text) {
            $payload["text"] = $text;
        }
        if (!is_null($preheader)) {
            $payload["preheader"] = $preheader;
        }
        if (!is_null($amp_html)) {
            $payload["amp_html"] = $amp_html;
        }

        if ($this->DEBUG) {
            $this->log_message(
                sprintf(
                    "updating template\n ID:%s\nVERSION:%s\n with name %s and subject %s\n",
                    $template_id,
                    $version_id,
                    $name,
                    $subject
                )
            );
        }

        return $this->api_request($endpoint, self::HTTP_PUT, $payload);
    }

    /**
     * Get Specific Email Log
     *
     * @param string $log_id the log getting retrieved
     * @return array API response object
     */
    public function get_log($log_id) {
        $endpoint = "logs/" . $log_id;

        return $this->api_request($endpoint, self::HTTP_GET);
    }

    /**
     * Get Specific Email's Events
     *
     * @param string $log_id the log getting retrieved
     * @return array API response object
     */
    public function get_events($log_id) {
        $endpoint = "logs/" . $log_id . "/events";

        return $this->api_request($endpoint, self::HTTP_GET);
    }

    /**
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

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * List drip campaigns
     *
     * @return array API response object
     */
    public function list_drip_campaigns(){
        $endpoint = "drip_campaigns";
        return $this->api_request($endpoint, self::HTTP_GET);
    }

    /**
     * List drip campaign details
     *
     * @param string $drip_campaign_id id of drip campaign
     * @return array API response object
     */
    public function drip_campaign_details($drip_campaign_id){
        $endpoint = "drip_campaigns/" . $drip_campaign_id;

        return $this->api_request($endpoint, self::HTTP_GET);
    }

    /**
     * Start on drip campaign
     *
     * The additional optional parameters for $args are as follows:
     *     'sender' - Default is null. Array ("address", "name", "reply_to") of sender.
     *     'cc' - Default is null. Array of ("address", "name") for carbon copy.
     *     'bcc' - Default is null. Array of ("address", "name") for blind carbon copy.
     *     'tags' - Default is null. Array of strings to tag email send with.
     *     'esp' - Default is null. Value of ("esp_account": "esp_id")
     *
     * @param array $recipient_address array of ("address", "name") to send to
     * @param string $drip_campaign_id drip campaign being added to
     * @param array (optional) $data email data being sent with drip
     * @param array (optional) $args additional options being sent with email (tags, cc's, etc)
     * @return array API response object
     */
    public function start_on_drip_campaign($recipient_address, $drip_campaign_id, $data=null, $args=null){
        $endpoint = "drip_campaigns/" . $drip_campaign_id . "/activate";

        $payload = array();
        if (is_array($recipient_address)) {
            $payload["recipient"] = $recipient_address;
        } else if (is_string($recipient_address)){
            $payload = array(
                "recipient_address" => $recipient_address
            );
        }

        if (is_array($data)) {
            $payload['email_data'] = $data;
        }

        if (is_array($args)) {
            $payload = array_merge($args, $payload);
        }

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Remove customer from drip campaign
     *
     * @param string $recipient_address email address being removed drip campaign
     * @param string $drip_campaign_id drip campaign being removed to
     * @return array API response object
     */
    public function remove_from_drip_campaign($recipient_address, $drip_campaign_id){
        $endpoint = "drip_campaigns/" . $drip_campaign_id . "/deactivate";

        $payload = array(
            "recipient_address" => $recipient_address
        );

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Remove customer from all drip campaigns
     *
     * @param string $recipient_address email address being removed from all drip campaigns
     * @return array API response object
     */
    public function remove_from_all_drip_campaigns($recipient_address){
        $endpoint = "drip_campaigns/deactivate";

        $payload = array(
            "recipient_address" => $recipient_address
        );

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Start Batch API transaction
     *
     * @return BatchAPI object
     */
    public function start_batch() {
        return new BatchAPI(
            $this->API_KEY,
            array(
                'API_HOST' => $this->API_HOST,
                'API_PROTO' => $this->API_PROTO,
                'API_PORT' => $this->API_PORT,
                'API_VERSION' => $this->API_VERSION,
                'DEBUG' => $this->DEBUG,
            )
        );
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
            $this->log_message(sprintf(
                "rendering template `%s` with \n%s",
                $email_id,
                print_r($payload, true)
            ));
        }

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Helper function to Base64 encode files and return the encoded data
     *
     * @param string $path Local path of the file to encode
     * @return string/false the encoded file data or false on failure
     */
    protected function encode_attachment($path) {
      if (!is_string($path)) {
          $e = sprintf("inline parameter must be path to file as string, received: %s", gettype($path));
          throw new API_Error($e);
      }

      $file_data = file_get_contents($path);

      return base64_encode($file_data);
    }

    protected function build_path($endpoint, $absolute = True) {
        $path = sprintf("/api/v%s/%s", $this->API_VERSION, $endpoint);
        if ($absolute) {
            $path = sprintf("%s://%s:%s%s",
                $this->API_PROTO,
                $this->API_HOST,
                $this->API_PORT,
                $path);
        }
        return $path;
    }

    protected function api_request($endpoint, $request = "POST", $payload = null, $params = null) {
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

            $this->log_message(sprintf("payload: %s\r\npath: %s\r\n", $payload_string, $path));
        }

        $code = null;
        try {
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = json_decode( $result );

            if ($code != 200) {
                throw new API_Error("Request was not successful", $code, $result, $response);
            }
        } catch (API_Error $e) {
            if ($this->DEBUG) {
                $this->log_message(
                    sprintf("Caught exception: %s\r\n%s", $e->getMessage(), print_r($e, true)),
                    self::LOG_ERR
                );
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

    /**
     * Log debug messages using the custom handler passed as an option in the constructor.
     * If not handler is defined it falls back to using 'error_log'.
     *
     * @param $message string the logged message
     * @param string $priority_level based on syslog priority levels http://php.net/manual/en/function.syslog.php
     * @return bool true on success or false on failure
     */
    protected function log_message($message, $priority_level = self::LOG_DEBUG)
    {
        if (
            $this->DEBUG &&
            $this->API_DEBUG_HANDLER &&
            is_callable($this->API_DEBUG_HANDLER)
        ) {
            $response = call_user_func($this->API_DEBUG_HANDLER, $message, $priority_level);
        } else {
            $response = error_log($message);
        }

        return $response;
    }
}


class BatchAPI extends API {
    private $commands;

    public function __construct($api_key, $options = array()) {
        parent::__construct($api_key, $options);
        $this->commands = array();
    }

    protected function api_request($endpoint, $request = "POST", $payload = null, $params = null) {
        $path = $this->build_path($endpoint, $absolute = false);

        if ($params) {
            $path = $path . '?' . http_build_query($params);
        }

        $command = array(
            'path' => $path,
            'method' => $request
        );

        // set payload
        if ($payload) {
            $command['body'] = $payload;
        }

        $this->commands[] = $command;

        return (object) array(
            'status' => 'Batched',
            'success' => true,
        );
    }

    /**
     * Execute all currently queued commands
     *
     * @return array BatchAPI response object.
     */
    public function execute() {
        $endpoint = "batch";
        $path = $this->build_path($endpoint);

        $ch = curl_init($path);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::HTTP_POST);

        // set payload
        $payload_string = json_encode($this->commands);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_string);

        // set headers
        $httpheaders = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload_string),
            $this->API_HEADER_KEY . ": " . $this->API_KEY,
            $this->API_HEADER_CLIENT . ": " . $this->API_CLIENT_STUB
        );

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/data/ca-certificates.pem');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);

        if ($this->DEBUG) {
            // enable curl verbose output to STDERR
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            $this->log_message(sprintf("payload: %s\r\npath: %s\r\n", $payload_string, $path));
        }

        $code = null;

        try {
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response = json_decode( $result );

            if ($code != 200) {
                throw new API_Error("Request was not successful", $code, $result, $response);
            }
        } catch (API_Error $e) {
            if ($this->DEBUG) {
                $this->log_message(
                    sprintf("Caught exception: %s\r\n%s", $e->getMessage(), print_r($e, true)),
                    self::LOG_ERR
                );
            }

            $response = (object) array(
                'code' => $code,
                'status' => "error",
                'success' => false,
                'exception' => $e
            );
        }

        curl_close($ch);
        $this->commands = array();

        return $response;
    }

    /**
     * Cancel any pending batched commands to be sent.
     *
     * @return object
     */
    public function cancel() {
        $this->commands = array();
        return (object) array(
            'code' => 0,  // Use 0 because we didn't even talk to the server.
            'status' => 'Canceled',
            'success' => true,
            'exception' => null
        );
    }

    public function command_length() {
        return count($this->commands);
    }
}

?>
