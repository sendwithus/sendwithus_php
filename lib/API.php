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

    public function send($email_name, $email_to, $context = [])
    {
        $endpoint = "send";

        $context["email_name"] = $email_name;
        $context["email_to"] = $email_to;

        return $this->api_request($endpoint, $context)
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

    private function api_request($endpoint, $context)
    {
        $path = $this->build_path($endpoint);

        // stub
        // @TODO: finish
    
    }
}

?>
