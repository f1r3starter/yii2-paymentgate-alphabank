Payment Gate for Alfabank
==========


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist veksoftware/yii2-paymentgate-alphabank "*"
```

or add

```
"veksoftware/yii2-paymentgate-alphabank": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
// config/main.php
<?php
    'components' => [
        'paymentgate_alphabank' => [
            'class' => '\paymentgate_alphabank\components\PaymentComponent',
            'userName' => 'my_login_at_service',
            'password' => 'my password at service',
            'gatewayUrl' => 'https://www.test.com/rest/',
            'returnUrl' => '',
        ]
    ]
```

Then you can use it in your code :

```php

<?php

?>
```
