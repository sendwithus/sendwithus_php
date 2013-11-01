sendwithus_php
==============

sendwithus PHP Client

## status
[![Build Status](https://travis-ci.org/sendwithus/sendwithus_php.png)](https://travis-ci.org/sendwithus/sendwithus_php)

## requirements
    curl library must be installed and enabled in php.ini

Install it via Composer
-----------------------

Add it to your composer.json
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/sendwithus/sendwithus_php"
        }
    ],
    "require": {
        "sendwithus/api": "dev-master"
    }
}
```
Then install it with 

    composer install


```php
// Yii Users
Yii::$classMap = array(
    'sendwithus\\API' => dirname($_SERVER['DOCUMENT_ROOT']) . '/path/to/sendwithus/lib/API.php'
);

// composer users
use sendwithus\API;

require_once 'vendor/autoload.php';


$API_KEY = 'THIS_IS_A_TEST_API_KEY';
$options = array(
    'DEBUG' => true
);

$api = new API($API_KEY, $options);

// Get emails
$response = $api->emails();

// Create emails
$response = $api->create_email('Email Name',
    'Email Subject',
    '<html><head></head><body>Valid HTML<body></html>',
    'Optional text content')

// We validate all html content


// Send request with REQUIRED parameters only
$response = $api->send('email_id', 
    array('address' => 'us@sendwithus.com')
);

// Send request with REQUIRED and OPTIONAL parameters
$response = $api->send('email_id', 
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'), 
    array('name' => 'Jimmy the snake'), 
    array(
        'name' => 'Company', 
        'address' => 'company@company.com', 
        'reply_to' => 'info@company.com')
); 

// WARNING !!!
// -----------
// The following example is NONFUNCTIONAL
// against our production API. CC/BCC is COMING SOON
$response = $api->send('email_id', 
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'), 
    array('name' => 'Jimmy the snake'), 
    array(
        'name' => 'Company', 
        'address' => 'company@company.com', 
        'reply_to' => 'info@company.com'),
    array(
        array(
            'name' => 'CC Name',
            'address' => 'CC@company.com'),
        array(
            'name' => 'CC 2 Name',
            'address' => 'CC2@company.com')),
    array(
        array(
            'name' => 'BCC Name',
            'address' => 'BCC@company.com'))
); 
```

## expected response

### Success
```php
print $response->success; // true
    
print $response->status; // "OK"

print $response->receipt_id; // ### numeric receipt_id you can use to query email status later
```

### Error cases
```php
print $response->success; // false

print $response->status; // "error"

print $response->exception; // Exception Object

print $response->code;
// 400 (malformed request)
// 403 (bad api key)
```

