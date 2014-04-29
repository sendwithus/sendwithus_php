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
    private $API_CLIENT_VERSION = "2.0.1";
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
     *     'files' - Default is null. Array of files to attach to email.
     *
     * @param string $email_id ID of email to send
     * @param array $recipient array of ("address", "name") to send to
     * @param array $args (optional) additional optional parameters
     * @return array API response object
     */
    public function send($email_id, $recipient, $args = null) {
        //in order to save backward compability detect old version of this function
        $args_list = func_get_args();
        $optional_keys = array(
            'email_data',
            'sender',
            'cc',
            'bcc',
            'inline',
            'tags',
            'files'
        );
        if( $this->is_send_v1($args_list, $optional_keys) ){
            //old order: $email_id, $recipient, $data=array(), $sender=null, $cc=null, $bcc=null, $inline=null
            $opts = array();
            //shift first two args
            array_shift($args_list);
            array_shift($args_list);
            foreach( $args_list as $num => $arg ){
                if( $arg ){
                    $opts[$optional_keys[$num]] = $arg;
                }
            }

            return $this->send( $email_id, $recipient, $opts );
        }
        var_dump($args_list);
        $endpoint = "send";

        $payload = array(
            "email_id" => $email_id,
            "recipient" => $recipient
        );

        if (is_array($args)) {
            $payload = array_merge($args, $payload);
        }

        // Optional inline attachment
        if(isset($payload['inline'])) {
            $inline_attachment_path = $payload['inline'];
        }

        if (isset($inline_attachment_path)) {
            if (!is_string($inline_attachment_path)) {
                $e = sprintf("inline parameter must be path to file as string, received: %s", gettype($inline_attachment_path));
                throw new API_Error($e);
            }
            $image = file_get_contents($inline_attachment_path);
            $encoded_image = base64_encode($image);
            $payload["inline"] = array(
                "id" => basename($inline_attachment_path),
                "data" => $encoded_image
            );
        }

        // Optional inline attachment
        if (isset($payload['files'])) {
            $attach_files = array();
            foreach( $payload['files'] as $file ){
                $f = file_get_contents($file);
                $encoded_file = base64_encode($f);
                $attach_files[] = array(
                    "id" => basename($file),
                    "data" => $encoded_file
                );
            }
            $payload['files'] = $attach_files;
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
        $endpoint = "emails";
        $payload = NULL;
        return $this->api_request($endpoint, $payload, null, "GET");
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
        $endpoint = "emails";

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

        return $this->api_request($endpoint, $payload);
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

    /**
     * Trying to detect old manner of call of the method 'send' according to arguments.
     * First of all we are checking arguments length ( it should be less then 4 for new version ),
     * then loop through 3rd argument ( data in old version, options in new )
     * in order to find one of the optional key of the parameter
     * If we found at least one optional parameter - this is new version.
     * @param $args
     * @param $optional_keys
     * @return bool
     */
    private function is_send_v1($args, $optional_keys){
        if( count( $args ) < 3 ){
            //less than 3 args both versions do the same
            return false;
        }
        if( count( $args ) > 3 ){
            return true;
        }
        if( count( $args ) == 3 ){
            if( !is_array( $args[2] ) ){
                return false;
            }
            foreach( $args[2] as $k => $v ){
                if( array_search( $k, $optional_keys ) !== false ){
                    return false;
                }
            }
            return true;
        }
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
        if ($payload && $request == "POST") {
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
