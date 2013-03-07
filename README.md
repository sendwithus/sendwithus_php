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

$r = $api->send('test', 'test@sendwithus.com', array('name' => 'Jimmy the snake'));

## expected response

### Success
print $r['status'];
-> OK

print $r['receipt_id'];
-> ### numeric receipt_id you can use to query email status later

### Error cases
print $r['status'];
-> error

print $r['exception'];
-> Exception Object

print $r['code'];
-> 404 (email_id not found)

