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

    protected $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    protected $API_HOST = 'api.sendwithus.com';
    protected $API_PORT = '443';
    protected $API_PROTO = 'https';
    protected $API_VERSION = '1';
    protected $API_HEADER_KEY = 'X-SWU-API-KEY';
    protected $API_HEADER_CLIENT = 'X-SWU-API-CLIENT';
    protected $API_CLIENT_VERSION = "3.0.0";
    protected $API_CLIENT_STUB = "php-%s";

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
            error_log(sprintf("sending email `%s` to \n", $email_id));
            error_log(print_r($recipient, true));
            if (isset($payload['sender'])) {
                error_log(sprintf("\nfrom\n"));
                error_log(print_r($payload['sender'], true));
            }
            error_log(sprintf("\nwith\n"));
            error_log(print_r($payload, true));
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
     * Customer Conversion
     *
     * @param string $email customer email
     * @param array $revenue Optional revenue cent value
     *
     * @return array API response object.
     */
    public function customer_conversion($email, $revenue=null) {
        $endpoint = "customers/" . $email . "/conversions";
        $payload = array("revenue" => $revenue);

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
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

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
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

        return $this->api_request($endpoint, self::HTTP_PUT, $payload);
    }

    /**
     * Get Email Send Logs
     *
     * @param string $count (optional) the number of logs to return. Max: 100
     * @param array $offset (optional) offset the number of logs to return
     * @param int created_gt (optional) return logs created strictly after the given UTC timestamp
     * @param int created_gte (optional) return logs created on or after the given UTC timestamp
     * @param created_lt (optional) return logs created strictly before the given UTC timestamp
     * @param created_lte (optional) return logs created on or before the given UTC timestamp
     * @return array API response object
     */
    public function logs($count = 100, $offset = 0, $created_gt = null, $created_gte = null, $created_lt = null, $created_lte = null) {
        $endpoint = "logs";

        $params = array(
            "count" => $count,
            "offset" => $offset
        );

        if (!is_null($created_gt)) {
            $params['created_gt'] = $created_gt;
        }
        if (!is_null($created_gte)) {
            $params['created_gte'] = $created_gte;
        }
        if (!is_null($created_lt)) {
            $params['created_lt'] = $created_lt;
        }
        if (!is_null($created_lte)) {
            $params['created_lte'] = $created_lte;
        }

        return $this->api_request($endpoint, self::HTTP_GET, null, $params);
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
     * @param string $recipient_address email address being added to drip campaign
     * @param string $drip_campaign_id drip campaign being added to
     * @param array (optional) $data email data being sent with drip
     * @param array (optional) $args additional options being sent with email (tags, cc's, etc)
     * @return array API response object
     */
    public function start_on_drip_campaign($recipient_address, $drip_campaign_id, $data=null, $args=null){
        $endpoint = "drip_campaigns/" . $drip_campaign_id . "/activate";

        $payload = array(
            "recipient_address" => $recipient_address
        );

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
     * Get ESP Accounts
     *
     * @param string (optional) $esp_type filter response to only return ESP accounts of a certain type
     * @return array API response object
     */
    public function get_esp_accounts($esp_type = null) {
        $endpoint = 'esp_accounts';

        $params = array();

        if (!is_null($esp_type)) {
            $params['esp_type'] = $esp_type;
        }

        return $this->api_request($endpoint, self::HTTP_GET, null, $params);
    }

    /**
     * Create ESP Account
     *
     * @param string $name the name of the ESP account
     * @param string $esp_type the type of ESP being created
     * @param array $credentials an array of credentials for the ESP type
     * @return array API response object
     */
    public function create_esp_account($name, $esp_type, $credentials) {
        $endpoint = 'esp_accounts';

        $payload = array(
            "name" => $name,
            "esp_type" => $esp_type,
            "credentials" => $credentials
        );

        return $this->api_request($endpoint, self::HTTP_POST, $payload);
    }

    /**
     * Set default ESP Account
     *
     * @param string $esp_id ID of the ESP Account
     * @return array API response object
     */
    public function set_default_esp_account($esp_id) {
        $endpoint = 'esp_accounts/set_default';

        $payload = array(
            "esp_id" => $esp_id
        );

        return $this->api_request($endpoint, self::HTTP_PUT, $payload);
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
            error_log(sprintf("rendering template `%s` with \n", $email_id));
            error_log(print_r($payload, true));
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

            error_log(sprintf("payload: %s\r\n", $payload_string));
            error_log(sprintf("path: %s\r\n", $path));
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

            error_log(sprintf("payload: %s\r\n", $payload_string));
            error_log(sprintf("path: %s\r\n", $path));
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
