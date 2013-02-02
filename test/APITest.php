<?php
/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

require 'lib/API.php';

$API_KEY = 'THIS_IS_A_TEST_API_KEY';
$options = array(
    'API_HOST' => 'beta.sendwithus.com',
    'API_PROTO' => 'http',
    'API_PORT' => '80',
    'DEBUG' => true
);

$api = new API($API_KEY, $options);

$r = $api->send('test', 'test@sendwithus.com', array('name' => 'Jimmy the snake'));

print_r ( $r );

?>
