<?php
/**
 * Send With Us PHP Client
 * @author matt@sendwithus.com
 */

require 'lib/API.php';

$API_KEY = 'THIS_IS_A_TEST_API_KEY';
$options = array(
    'DEBUG' => true
);

$api = new \sendwithus\API($API_KEY, $options);

$r = $api->send('test', 'test@sendwithus.com', array('name' => 'Jimmy the snake'));

print $r;
print_r ( $r );

?>
