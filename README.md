sendwithus_php
==============

sendwithus PHP Client

## status
BETA - this client is functional and tested

## requirements
    curl library must be installed and enabled in php.ini

## usage
    // Yii Users
    Yii::$classMap = array(
        'sendwithus\\API' => dirname($_SERVER['DOCUMENT_ROOT']) . '/path/to/sendwithus/lib/API.php'
    );

    // Otherwise
    require 'lib/API.php';

    $API_KEY = 'THIS_IS_A_TEST_API_KEY';
    $options = array(
        'DEBUG' => true
    );

    $api = new \sendwithus\API($API_KEY, $options);

    // Request with REQUIRED parameters only
    $response = $api->send('email_id', 
        array('address' => 'us@sendwithus.com'), 
        array('name' => 'Jimmy the snake')
    ); 

    // Request with REQUIRED and OPTIONAL parameters
    $response = $api->send('email_id', 
        array(
            'name' => 'Matt', // recipient `name` is optional
            'address' => 'us@sendwithus.com'), 
        array('name' => 'Jimmy the snake'), 
        array( // `sender` array is optional, overrides sendwithus.com configuration
            'name' => 'Company', 
            'address' => 'company@company.com', 
            'reply_to' => 'info@company.com') 
    ); 

## expected response

### Success
    print $response->success;
    -> true
    
    print $response->status;
    -> "OK"

    print $response->receipt_id;
    -> ### numeric receipt_id you can use to query email status later

### Error cases
    print $response->success;
    -> false

    print $response->status;
    -> "error"

    print $response->exception;
    -> Exception Object

    print $response->code;
    -> 400 (malformed request)
    -> 403 (bad api key)
    -> 404 (email_id not found)

