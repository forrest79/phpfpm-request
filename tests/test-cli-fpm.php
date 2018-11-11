<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Forrest79\PhpFpmRequest;

if (PHP_SAPI === 'cli') {

	echo PhpFpmRequest\Requester::autodetect()
		->setPhpFile(__FILE__)
		->send()
		->getBody() . PHP_EOL;

} else {

	echo 'This code is processed with php-fpm. You can warm up your cache here, clean cache, etc.';

}
