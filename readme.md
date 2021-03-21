# PhpFpmRequest

[![Latest Stable Version](https://poser.pugx.org/forrest79/phpfpm-request/v)](//packagist.org/packages/forrest79/phpfpm-request)
[![Monthly Downloads](https://poser.pugx.org/forrest79/phpfpm-request/d/monthly)](//packagist.org/packages/forrest79/phpfpm-request)
[![License](https://poser.pugx.org/forrest79/phpfpm-request/license)](//packagist.org/packages/forrest79/phpfpm-request)
[![Build](https://github.com/forrest79/PhpFpmRequest/actions/workflows/build.yml/badge.svg?branch=master)](https://github.com/forrest79/PhpFpmRequest/actions/workflows/build.yml)

Simply utility to make `php-fpm` requests right from command line with no needs to setup your web server for this requests.


## Installation

The recommended way to install Forrest79/PhpFpmRequest is through Composer:

```sh
composer require forrest79/phpfpm-request
```

This also needs `cgi-fcgi` installed on your system. On Debian (Ubuntu...) like linux you can do this:

```bash
sudo apt-get install libfcgi0ldbl
```

You can you this to clear some caches from cli, that needs to be call via HTTP request (opcache, apcu, ...) or to warm up your cache after new version is deployed and before web server is started to point to new source code.


## How to use it

We just need to know, where is `php-fpm` listening. It's config directive `listen` and it could be `socket` or `TCP\IP`.
In `nginx` it's directive `fastcgi_pass`. If you don't know that or you're planning to run this on different systems, you can try to autodetect this:

```php
$requester = Forrest79\PhpFpmRequest\Requester::autodetect();
```

Or if you know where `php-fpm` is listening, you can use this value:

```php
$requester = Forrest79\PhpFpmRequest\Requester::create('/var/run/php/php7.4-fpm.sock');
```

Now just simple set PHP file to process with `php-fpm`:

```php
$requester->setPhpFile('/var/www/index.php');
```

And send the request:

```php
$response = $requester->send();
```

Now we have `Response` object, that can return `array` of `HTTP` headers and text body.

```php
echo $reponse->getBody() . PHP_EOL;

foreach ($response->getHeaders() as $header) {
    echo $header . PHP_EOL;
}
```

If you need to pass more options to `php-fpm`, just use `setOption(string $name, string $value)` method. Only `REQUEST_METHOD` is set automatically to `GET` and `SCRIPT_FILENAME` is pass. But you can add anything you want:

```php
$requester
    ->setOption('QUERY_STRING', '?param=1')
    ->setOption('SERVER_NAME', 'my-server.com')
    ->setOption('REQUEST_URI', '/show-detail/');
```

If you need better API for this, extends `Requester` with your own class and create better public API and in every method just call `parent::setOption('...', '...')`.


### Cli and php-fpm in one file

You can have `cli` and `php-fpm` source code in one file:

```php
if (PHP_SAPI === 'cli') {
    echo Forrest79\PhpFpmRequest\Requester::autodetect()
        ->setPhpFile(__FILE__)
        ->send()
        ->getBody() . PHP_EOL;
} else {
    echo 'This code is processed with php-fpm. You can warm up your cache here, clean cache, etc.';
}
```
