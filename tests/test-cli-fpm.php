<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Forrest79\PhpFpmRequest;

$message = 'This code is processed with php-fpm. You can warm up your cache here, clean cache, etc.';

if (PHP_SAPI === 'cli') {

	$response = PhpFpmRequest\Requester::autodetect()
		->setPhpFile(__FILE__)
		->send()
		->getBody();

	echo $response . PHP_EOL;
	if ($response !== $message) {
		exit(1);
	}

} else {

	echo $message;

}
