sendwithus_php
==============

Sendwithus PHP Client

## Status
[![Build Status](https://travis-ci.org/sendwithus/sendwithus_php.png)](https://travis-ci.org/sendwithus/sendwithus_php)

## Requirements
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


## Getting started

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
```

# Emails

## Get emails

```php
$response = $api->emails();
```

## Get specific template
```php
$response = $api->get_template($template_id,     //string id of template
                               $version_id       //optional string version id of template
);
```


## Create emails
### Create new email
_We validate all HTML content_
```php
$response = $api->create_email('Email Name',               // string email name
    'Email Subject',                                       // string subject line of email
    '<html><head></head><body>Valid HTML<body></html>',    // string of HTML code for email
    'Optional text content')                               // optional string of text for email
```

### Create new email template version
_We validate all HTML content_
```php
$response = $api->create_new_template_version(
    'Email Name',                                          // string email version name
    'Email Subject',                                       // string subject of email
	'tem_JAksjdjwJXUVwnemljflksEJks',                      // string id of email used
    '<html><head></head><body>Valid HTML<body></html>',    // string block of HTML code used for email
    'Optional text content')                               // optional string of text used for email
```

### Update email version
_We validate all HTML content_
```php
$response = $api->update_template_version(
    'Email Name',                                          // string email version name
    'Email Subject',                                       // string subject of email
	'tem_JAkCjdjwJXUVwnemljflksEJks',                      // string id of email being updated
	'ver_iuweJskj4Jwkj2ndclk4jJDken',                      // string version of email being updated
    '<html><head></head><body>Valid HTML<body></html>',    // string block of HTML code used for email
    'Optional text content')                               // optional string of text used for email
```


## Send emails

*NOTE* - If a customer does not exist by the specified email (recipient address), the send call will create a customer.

```php
// Send function header
send(
    $email_id,      // string, id of email to send (template id)
    $recipient,     // associative array, ("address" => "ckent@dailyplanet.com", "name" => "Clark") to send to
    $args           // (optional) array, (array) additional parameters - (see below)
)

// Send function options
'template_data'  // array of variables to merge into the template.
'sender'         // array ("address", "name", "reply_to") of sender.
'cc'             // array of ("address", "name") for carbon copy.
'bcc'            // array of ("address", "name") for blind carbon copy.
'inline'         // string, path to file to include inline
                 // or an associative array with "id" containing filename
                 // and "data" containing base64 encoded file content
'files'          // array, each element represents either a string path to file to attach
                 // or an associative array with "id" containing filename
                 // and "data" containing base64 encoded file content
'tags'           // array of strings to tag email send with.
'esp_account'    // string of ESP ID to manually select ESP
'headers'        // associative array of header name and value
```

## Send Examples

### Send request with REQUIRED parameters only

```php
$response = $api->send('email_id',
    array('address' => 'us@sendwithus.com')
);
```

### Send request with REQUIRED and OPTIONAL parameters

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'),
    array(
    	'template_data' => array('name' => 'Jimmy the snake'),
    	'sender' => array(
            'name' => 'Company',
            'address' => 'company@company.com',
            'reply_to' => 'info@company.com'
        ),
        'esp_account' => 'esp_EMpi5eo59cG4cCWd7AdW7J'
    )
);
```

### Send an email with multiple CC/BCC recipients

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'
    ),
    array(
        'template_data' => array('name' => 'Jimmy the snake'),
        'sender' => array(
            'name' => 'Company',
            'address' => 'company@company.com',
            'reply_to' => 'info@company.com'
        ),
        'cc' => array(
            array(
                'name' => 'CC Name',
                'address' => 'CC@company.com'
            ),
            array(
                'name' => 'CC 2 Name',
                'address' => 'CC2@company.com'
            )
        ),
        'bcc' => array(
            array(
                'name' => 'BCC Name',
                'address' => 'BCC@company.com'
            )
        )
    )
);
```

### Send an email with a dynamic tag

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'),
    array(
        'tags' => array('Production', 'Client1')
    )
);
```

### Send specific version of an email

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'),
    array(
        'version_name' => 'My Version'
    )
);
```

### Send email with an inline image attachment

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'),
    array(
        'inline' => 'filename.jpg'
    )
);
```

### Send email with an inline encoded image attachment

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'),
    array(
        'inline' => array(
            'id' => 'photo.jpg',
            'data' => base64_encode(file_get_contents('filename.jpg'))
        )
    )
);
```

### Send email with attachments

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'),
    array(
        'files' => array(
            'filename.txt',
            'filename.pdf',
            array(
                'id' => 'photo.jpg',
                'data' => base64_encode(file_get_contents('filename.jpg'))
            )
        )
    )
);
```


## Render templates

```php
// Render function header
render(
    $email_id,      // string, id of email to send (template id)
    $args           // (optional) array, (array) additional parameters - (see below)
)

// Send function options
'template_data'  // Array of variables to merge into the template.
'version_id'     // Version ID obtained from /templates/(:template_id)/versions
'version_name'   // Version name that you want rendered (provide either a version_name or a version_id, not both)
'locale'         // Template locale to render
'strict'         // Render in strict mode (fails on missing template data)
```

### Example:

```php
$response = $api->render('email_id',
    array('address' => 'us@sendwithus.com'),
    array(
        'template_data' => array(
            'name' => 'Bobby Boucher'
        )
    )
);
```

## Get Email Logs
```php
logs(
    $count             //The number of logs to return. Max: 100, Default: 100
    $offset            //Offset the number of logs to return. Default: 0
    $created_gt        //Return logs created strictly after the given UTC timestamp
    $created_gte       //Return logs created on or after the given UTC timestamp
    $created_lt        //Return logs created strictly before the given UTC timestamp
    $created_lte       //Return logs created on or before the given UTC timestamp
)
```
## Get a Specific Email's Log
```php
get_log(
    $log_id          // id of log to retrieve
)
```

Example

```php
$response = api->get_log('log_d4R7hV4d0r')
```

Response

```php
(
    [email_id] => tem_1jeid84bg
    [recipient_name] =>
    [message] => Mandrill: Message has been successfully delivered to the receiving server.
    [id] => log_d4R7hV4d0r
    [object] => log
    [created] => 1409287597
    [email_name] => test
    [recipient_address] => person@example.com
    [status] => sent
    [email_version] => Original Version
)
```

## Resend a Specific Email from Log
```php
resend(
    $log_id          // id of log to resend
)
```

Example

```php
$response = api->resend('log_d4R7hV4d0r')
```

Response

```php
(
    [status] => OK
    [receipt_id] => 130be975-dc07-4071-9333-58530e5df052-i03a5q
    [email] => stdClass Object
        (
            [locale] => en-US
            [version_name] => Test Template
            [name] => test
        )

    [success] => 1
)
```

## Drip Unsubscribe
```php
// Unsubscribe email address from active drips
drip_unsubscribe(
    $email_address,      // the email to unsubscribe from active drips
)
```

## Drip Unsubscribe Example

```php
$response = $api->drip_unsubscribe('us@sendwithus.com');
```

## Drips 2.0

### List Drip Campaigns
List all drip campaigns for the current profile

Example

```php
$response = $api->list_drip_campaigns();
```

Response

```php
Array
(
    [0] => stdClass Object
        (
            [drip_steps] => Array
                (
                    [0] => stdClass Object
                        (
                            [id] => dcs_1234abcd1234
                            [object] => drip_step
                            [delay_seconds] => 0
                            [email_id] => tem_1234abcd1234
                        )

                )

            [name] => Drip Campaign
            [enabled] => 1
            [id] => dc_1234abcd1234
            [trigger_email_id] => tem_1234abcd1234
            [object] => drip_campaign
        )
)
```

### Start on Drip Campaign
Starts a customer on the first step of a specified drip campaign

```php
start_on_drip_campaign(
    $recipient_address, // string, email address being added to drip campaign
    $drip_campaign_id,  // string, drip campaign being added to
	$data               // array, (optional) email data being added to drip campaign
	$args               // array, (optional) additional options being sent with email (tags, cc's, etc)
);

// Args options
'sender'      // array ("address", "name", "reply_to") of sender.
'cc'          // array of ("address", "name") for carbon copy.
'bcc'         // array of ("address", "name") for blind carbon copy.
'tags'        // array of strings to tag email send with.
'esp_account' // string of ESP ID to manually select ESP
```

Example

```php
$template_data = array(
    'name' => 'Jean-Luc'
	'rank' => 'Captain'
);

$args = array(
    'tags' => array('all', 'the', 'tags'),
    'cc' => array('address' => 'them@sendwithus.com')
);
$response = $api->start_on_drip_campaign('us@sendwithus.com', 'dc_1234abcd1234', $template_data, $args);
```

Response

```php
stdClass Object
(
    [success] => 1
    [drip_campaign] => stdClass Object
        (
            [id] => dc_1234abcd1234
            [name] => Drip Campaign
        )

    [message] => Recipient successfully added to drip campaign.
    [status] => OK
    [recipient_address] => us@sendwithus.com
)
```

### Remove from Drip Campaign
Deactivates all pending emails for a customer on a specified drip campaign
```php
$response = $api->remove_from_drip_campaign(
    $recipient_address, // string, email address being removed from drip campaign
    $drip_campaign_id   // string, drip campaign being removed from
);
```

Example

```php
$response = $api->remove_from_drip_campaign('us@sendwithus.com', 'dc_1234abcd1234');
```

Response

```php
stdClass Object
(
    [success] => 1
    [drip_campaign] => stdClass Object
        (
            [id] => dc_1234abcd1234
            [name] => Drip Campaign
        )

    [message] => Recipient successfully removed from drip campaign.
    [status] => OK
    [recipient_address] => us@sendwithus.com
)
```

### List Drip Campaign Details
Show all the steps and other information in a specified campaign
```php
$response = $api->drip_campaign_details(
    $drip_campaign_id   // string, drip campaign to list details from
);
```

Example

```php
$response = $api->drip_campaign_details('dc_1234abcd1234');
```

Response

```php
stdClass Object
(
    [drip_steps] => Array
        (
            [0] => stdClass Object
                (
                    [id] => dcs_1234abcd1234
                    [object] => drip_step
                    [delay_seconds] => 0
                    [email_id] => tem_1234abcd1234
                )

        )

    [name] => Drip Campaign
    [enabled] => 1
    [id] => dc_1234abcd1234
    [trigger_email_id] => tem_1234abcd1234
    [object] => drip_campaign
)

```

## Customers API

### Create Customer
```php
create_customer(
    $email,             // string, email of customer
    $data,              // array, optional, data for customer
    $args               // array, optional, optional parameters:

    // The additional optional parameters are as follows:
    //      'locale' - Default is null. String to specify a locale for this customer.
)
```

Example

```php
$response = $api->create_customer('us@sendwithus.com',
    array('name' => 'Sendwithus')
);
```

### Update Customer
```php
update_customer(
    $email,             // string, email of customer
    $data,              // array, optional, data for customer
)
```

Example

```php
$response = $api->update_customer('us@sendwithus.com',
    array('name' => 'Sendwithus.com')
);
```

### Delete Customer
```php
delete_customer(
    $email,             // string, email of customer
)
```

Example

```php
$response = $api->delete_customer('us@sendwithus.com');
```

### List Customer Logs
List all customer logs

Example

```php
$response = api->get_customer_logs("email@email.com");

print_r($response);

/*
(
    [success] => 1
    [logs] => Array
        (
        [email_name] => Name of email
        [message] => Message body
        [recipient_name] => Recipient name
        [email_version] => Name of email version
        [object] => log
        [email_id] => ID of email
        [created] => Time stamp
        [recipient_address] => Email address of recipient
        [status] => Status of email
        [id] => ID of log
        )
    [status] => OK
)
*/

```

## Batch API
Batch requests together to be run all at once.

### Usage
Create a batch_api object by calling `start_batch()`.

Do any request you would do normally with the API but on the batch_api object.

Execute all commands at once by calling `execute()` on the object.

### Example
```php
$batch_api = api->start_batch();
for($i = 0; $i < 10; $i++) {
    $result = $batch_api->create_customer('us@sendwithus.com',
        array('name' => 'Sendwithus'));
    // $result->success == true && $result->status == 'Batched'
}
$result = $batch_api->execute();

// $result will be an array of responses for each command executed.

```

### Canceling Batch Request
Sometimes it is necessary to cancel all the api requests that have been batched, but not yet sent.
To do that, use `cancel()`:

### Example
```php
$batch_api = api->start_batch();
for($i = 0; $i < 10; $i++) {
    $batch_api->create_customer('us@sendwithus.com',
        array('name' => 'Sendwithus'));
}
$result = $batch_api->cancel();
// $result->success == true && $result->status == 'Canceled'
```

Once you have canceled a batch, you can continue to use the batch to make more requests.

## Tests

### Running Unit Tests

Make sure to have phpunit installed (http://phpunit.de/) and run the following from the root directory

```php
phpunit test
```

# Troubleshooting

## General Troubleshooting

-   Enable debug mode
-   Make sure you're using the latest PHP client
-   Make sure `data/ca-certificate.pem` is included. This file is *required*
-   Capture the response data and check your logs &mdash; often this will have the exact error

## Enable Debug Mode

Debug mode prints out the underlying cURL information as well as the data payload that gets sent to Sendwithus. You will most likely find this information in your logs. To enable it, simply put `"DEBUG" => true` in the optional parameters when instantiating the API object. Use the debug mode to compare the data payload getting sent to [sendwithus' API docs](https://www.sendwithus.com/docs/api "Official Sendwithus API Docs").

```php
$API_KEY = 'THIS_IS_AN_EXAMPLE_API_KEY';
$options = array(
    'DEBUG' => true
);

$api = new API($API_KEY, $options);
```

## Response Ranges

Sendwithus' API typically sends responses back in these ranges:

-   2xx – Successful Request
-   4xx – Failed Request (Client error)
-   5xx – Failed Request (Server error)

If you're receiving an error in the 400 response range follow these steps:

-   Double check the data and ID's getting passed to Sendwithus
-   Ensure your API key is correct
-   Make sure there's no extraneous spaces in the id's getting passed

*Note*: Enable Debug mode to check the response code.
