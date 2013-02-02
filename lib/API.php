<?php
/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

class API 
{
    private $API_KEY;
    private $API_HOST;
    private $API_PORT;
    private $API_PROTO;
    private $API_VERSION;
    private $API_HEADER_KEY;
    private $API_CLIENT_VERSION = "0.1.0";

    private DEBUG = false;

    public function __construct($api_key, $options = [])
    {
        $this->$API_KEY = $api_key;

        foreach ($options as $key => $value)
        {
            $this->$$key = $value;
        }
    
    }

    public function send($email_name, $email_to, $data = [])
    {
        $endpoint = "send";

        $payload = array(
            "email_name" => $email_name,
            "email_to" => $email_to,
            "email_data" => $data
        );


        return $this->api_request($endpoint, $data)
    }

    private function build_path($endpoint)
    {
        $path = sprintf("%s://%s:%s/api/v%s/%s", 
            $this->API_PROTO, 
            $this->API_HOST, 
            $this->API_PORT, 
            $this->API_VERSION, 
            endpoint);

        return $path
    }

    private function api_request($endpoint, $payload)
    {
        $path = $this->build_path($endpoint);

        $options = array(
            'http' => array(
                'method'  => 'POST',
                'content' => json_encode($payload),
                'header'=>  "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n" .
                "X-SWU-API-KEY: " . $this->API_KEY . "\r\n"
            )
        );

        $context  = stream_context_create( $options );
        $result = file_get_contents( $path, false, $context );

        $response = json_decode( $result );
        return $response;
    }
}

?>
