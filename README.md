sendwithus_php
==============

sendwithus PHP Client

## status
ALPHA - this client is functional

## usage
    require 'lib/API.php';

    $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    $options = array(
        'DEBUG' => true
    );

    $api = new \sendwithus\API($API_KEY, $options);

    $response = $api->send('`email_id`', 'test@sendwithus.com', array('name' => 'Jimmy the snake'));

## expected response

### Success
    print $response['status'];
    -> OK

    print $response['receipt_id'];
    -> ### numeric receipt_id you can use to query email status later

### Error cases
    print $response['status'];
    -> error

    print $response['exception'];
    -> Exception Object

    print $response['code'];
    -> 400 (malformed request)
    -> 403 (bad api key)
    -> 404 (email_id not found)

