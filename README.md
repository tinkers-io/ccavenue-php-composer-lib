# ccavenue-php-composer-lib

### Description
This is a composer supported library for ccavenue payment gateway.

### Installation

#### With Composer (Recommended)
To install with composer you need to have [composer](https://getcomposer.org/) installed on your system.<br>

run this command:<br>
`composer require tinkers/ccavenue-php-composer-lib` <br>

**OR**

add this inside your composer.json section: <br>

```js
    {
        "require": {
           "tinkers/ccavenue-php-composer-lib": "^1.0"
           /*....*/
        }
    }
```

### Usage

```php
<?php
use tinkers\ccavenue\CCAvenue;

$cCAvenue = new CCAvenue(CCAvenue::BILLING_PAGE, [
            'merchant_id' => 'xxxxxx',
            'access_code' => 'xxxxxxxxxxxxxxxxx',
            'working_key' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        ], true);
        
$request = $cCAvenue->requestGenerator($requestData); // accepts billing page post data and provides array encrypted data and form-action/iframe url

```

Above `CCAvenue::requestGenerator()` will output:

```php
    [
        'encrypted_data' => 'form encrypted_data',
        'access_code' => 'access_code',
        'form_action' => 'https://secure.ccavenue.com/transaction/transaction.do?command=initiateTransaction',
    ]
```

this output can be used to generate request handler form.
