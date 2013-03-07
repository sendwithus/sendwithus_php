<?php namespace sendwithus;
/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

class API 
{
    private $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    private $API_HOST = 'beta.sendwithus.com';
    private $API_PORT = '443';
    private $API_PROTO = 'https';
    private $API_VERSION = '0';
    private $API_HEADER_KEY = 'X-SWU-API-KEY';
    private $API_CLIENT_VERSION = "0.1.0";

    private $DEBUG = false;

    public function __construct($api_key, $options = array())
    {
        $this->API_KEY = $api_key;

        foreach ($options as $key => $value)
        {
            $this->$key = $value;
        }
    
    }

    public function send($email_id, $email_to, $data = array())
    {
        $endpoint = "send";

        $payload = array(
            "email_id" => $email_id,
            "email_to" => $email_to,
            "email_data" => $data
        );

        if ($this->DEBUG) {
            printf("sending email `%s` to `%s` with \n", $email_name, $email_to);
            print_r ( $payload );
        }


        return $this->api_request($endpoint, $payload);
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

    private function api_request($endpoint, $payload)
    {
        $path = $this->build_path($endpoint);
        $response = array();
        $payload_string = json_encode($payload);

        $ch = curl_init($path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload_string),
            $this->API_HEADER_KEY . ": " . $this->API_KEY)
        );

        if ($this->DEBUG) {
            print_r($payload_string);
            print_r($path);
            print "\n";
        }

        try {
            //$result = file_get_contents( $path, false, $context );
            //$result = fopen($path, 'r', false, $context);
            $result = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
            $response = json_decode( $result );

            print_r($result);
            print_r($code);
            print_r($response);

            if ($code != 200) {
                throw new \Exception("Request was not successful " . $code);
            }
        } catch (\Exception $e) {
            if ($this->DEBUG) {
                printf("Caught exception: %s" % $e);
            }

            $response['code'] = $code;
            $response['status'] = "error";
            $response['message'] = $e;
        }

        curl_close($ch);

        return $response;
    }
}

?>
