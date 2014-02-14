<?php

namespace sendwithus;

require 'Error.php';

/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

class API
{
    private $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    private $API_HOST = 'api.sendwithus.com';
    private $API_PORT = '443';
    private $API_PROTO = 'https';
    private $API_VERSION = '1';
    private $API_HEADER_KEY = 'X-SWU-API-KEY';
    private $API_HEADER_CLIENT = 'X-SWU-API-CLIENT';
    private $API_CLIENT_VERSION = "1.0.4";
    private $API_CLIENT_STUB = "php-%s";

    private $DEBUG = false;

    public function __construct($api_key, $options = array())
    {
        $this->API_KEY = $api_key;
        $this->API_CLIENT_STUB = sprintf($this->API_CLIENT_STUB,
            $this->API_CLIENT_VERSION);

        foreach ($options as $key => $value)
        {
            $this->$key = $value;
        }
    }

    public function send($email_id, $recipient, $data=array(), $sender=null,
        $cc=null, $bcc=null, $inline=null)
    {
        $endpoint = "send";

        if (is_null($data)) {
            $data = array();
        };

        $data = (object)$data;

        $payload = array(
            "email_id" => $email_id,
            "recipient" => $recipient,
            "email_data" => $data
        );

        // set optional sender
        if ($sender)
        {
            $payload["sender"] = $sender;
        }

        // set optional cc
        if ($cc)
        {
            if (!is_array($cc))
            {
                $e = sprintf("cc parameter must be array, received: %s", gettype($cc));
                throw new API_Error($e);
            }
            $payload["cc"] = $cc;
        }

        // set optional bcc
        if ($bcc)
        {
            if (!is_array($bcc))
            {
                $e = sprintf("bcc parameter must be array, received: %s", gettype($bcc));
                throw new API_Error($e);
            }
            $payload["bcc"] = $bcc;
        }

        // Optional inline attachment
        if ($inline) {
            if (!is_string($inline))
            {
                $e = sprintf("inline parameter must be path to file as string, received: %s", gettype($inline));
                throw new API_Error($e);
            }
            $image = file_get_contents($inline);
            $encoded_image = base64_encode($image);
            $payload["inline"] = array(
                "id" => basename($inline),
                "data" => $encoded_image
            );
        }

        if ($this->DEBUG) {
            error_log(sprintf("sending email `%s` to \n", $email_id));
            error_log(print_r($recipient, true));
            if ($sender)
            {
                error_log(sprintf("\nfrom\n"));
                error_log(print_r($sender, true));
            }
            error_log(sprintf("\nwith\n"));
            error_log(print_r($payload, true));
        }


        return $this->api_request($endpoint, $payload);
    }

    public function emails()
    {
        $endpoint = "emails";
        $payload = NULL;
        return $this->api_request($endpoint, $payload, null, "GET");
    }

    public function create_email($name, $subject, $html, $text=null)
    {
        $endpoint = "emails";

        $payload = array(
            "name" => $name,
            "subject" => $subject,
            "html" => $html
        );

        // set optional text
        if ($text)
        {
            $payload["text"] = $text;
        }

        if ($this->DEBUG) {
            error_log(sprintf("creating email with name %s and subject %s\n", $name, $subject));
        }

        return $this->api_request($endpoint, $payload);
    }

    public function logs($count=100, $offset=0)
    {
        $endpoint = "logs";

        $params = array(
            "count" => $count,
            "offset" => $offset
        );

        return $this->api_request($endpoint, null, $params, "GET");
    }

    private function build_path($endpoint)
    {
        $path = sprintf("%s://%s:%s/api/v%s/%s",
            $this->API_PROTO,
            $this->API_HOST,
            $this->API_PORT,
            $this->API_VERSION,
            $endpoint);

        return $path;
    }

    private function api_request($endpoint, $payload, $params=null, $request="POST")
    {
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
        if ($payload && $request=="POST")
        {
            $httpheaders = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload_string),
                $this->API_HEADER_KEY . ": " . $this->API_KEY,
                $this->API_HEADER_CLIENT . ": " . $this->API_CLIENT_STUB
                );
        }
        else
        {
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