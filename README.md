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
```php
// Send function header
send(
    $email_id,      // string, id of email to send
    $recipient,     // associative array, ("address" => "ckent@dailyplanet.com", "name" => "Clark") to send to
    $args           // (optional) array, (array) additional parameters - (see below)
)

// Send function options
'email_data'  // array of variables to merge into the template.
'sender'      // array ("address", "name", "reply_to") of sender.
'cc'          // array of ("address", "name") for carbon copy.
'bcc'         // array of ("address", "name") for blind carbon copy.
'inline'      // string, path to file to include inline.
'files'       // array, paths to files to attach to the send.
'tags'        // array of strings to tag email send with.
'esp_account' // string of ESP ID to manually select ESP
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
        'email_data' => array('name' => 'Jimmy the snake'),
        'sender' => array(
            'name' => 'Company',
            'address' => 'company@company.com',
            'reply_to' => 'info@company.com'
			)
		'esp_account' => 'esp_EMpi5eo59cG4cCWd7AdW7J'
    )
);
```

### Send an email with multiple CC/BCC recipients

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'),
    array(
        'email_data' => array('name' => 'Jimmy the snake'),
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

### Send email with attachments

```php
$response = $api->send('email_id',
    array(
        'name' => 'Matt',
        'address' => 'us@sendwithus.com'),
    array(
        'files' => array(
            'filename.txt',
            'filename.pdf'
        )
    )
);
```

## Segments

### Get segments
```php
get_segments()
```

Example

```php
$response = $api->get_segments();
```

Response

```php
Array
(
    [0] => stdClass Object
        (
            [created] => 1402378620
            [id] => seg_0biVV4Ncf1234
            [name] => TEST_SEGMENT
            [object] => segment
        )

)
```

### Send to a Segment
```php
send_segment(
    $email_id,          // id of template to send
    $segment_id,        // id of the segment to send to
    $data               // optional array of data to send
)
```

Example

```php
$response = $api->send_segment(tem_123jeDI23, 'seg_0biVV4Ncf1234');
```

## Get an Email Log
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
$email_data = array(
    'name' => 'Jean-Luc'
	'rank' => 'Captain'
);

$args = array(
    'tags' => array('all', 'the', 'tags'),
    'cc' => array('address' => 'them@sendwithus.com')
);
$response = $api->start_on_drip_campaign('us@sendwithus.com', 'dc_1234abcd1234', $email_data, $args);
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
    //      'groups' - Default is null. Array of group IDs
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

### Add Customer to Group
Adds a customer to a specified group

```php
add_customer_to_group('curtis@sendwithus.com', 'grp_jk2jejidi3');
```

### Remove Customer From Group
Removes a customer from a specified group

```php
remove_customer_from_group('curtis@sendwithus.com', 'grp_jk2jejidi3');
```

## Groups API

### List Groups
List all groups

Example

```php
$response = $api->list_groups();

print_r($response->groups[0]);

/*
(
    [id] => grp_nEozfaiWwBHW4Fsmc
    [description] => An example group
    [name] => Group name
)
*/

```

### Create a Group
```php
create_group(
    $name,             // string, name of group
    $description,      // string, optional, description for group
)
```

Example

```php
$response = $api->create_group('Group name',
    'An example group'
);

print_r($response);

/*
stdClass Object
(
    [success] => 1
    [group] => stdClass Object
        (
            [name] => Group name
            [description] => An example group
            [id] => grp_ooEDQKetS2Yqs7FSGAdReB
        )

    [status] => OK
)
*/
```

### Update a Group
```php
update_group(
    $name,             // string, name of group
    $group_id          // string id of group
    $description,      // string, optional, description for group
)
```

Example

```php
$response = $api->update_group('Updated Group name',
    'An example group updated description'
);

print_r($response);

/*
stdClass Object
(
    [success] => 1
    [group] => stdClass Object
        (
            [name] => Updated Group name
            [description] => An example group updated description
            [id] => grp_ooEDQKetS2Yqs7FSGAdReB
        )

    [status] => OK
)
*/
```

### Delete Group
```php
delete_group(
    $group_id,             // string, group_id of group
)
```

Example

```php
$response = $api->delete_group('grp_ooEDQKetS2Yqs7FSGAdReB');
```


## Expected response

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

## Tests

### Running Unit Tests

Make sure to have phpunit installed (http://phpunit.de/) and run the following from the root directory

```php
phpunit test
```

