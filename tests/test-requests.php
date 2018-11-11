<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Forrest79\PhpFpmRequest;

$response1 = PhpFpmRequest\Requester::autodetect()
	->setPhpFile(__DIR__ . DIRECTORY_SEPARATOR . 'request-text.php')
	->send();

if ($response1->getBody() !== 'OK-TEXT') {
	echo sprintf('Bad response body: \'%s\', expected \'%s\'', $response1->getBody(), 'OK-TEXT') . PHP_EOL;
	exit(1);
}
if (count($response1->getHeaders()) !== 1) {
	echo 'Bad header count: ' . implode(', ', $response1->getHeaders()) . PHP_EOL;
	exit(1);
}

echo 'Response 1 is OK' . PHP_EOL;

$response2 = PhpFpmRequest\Requester::autodetect()
	->setPhpFile(__DIR__ . DIRECTORY_SEPARATOR . 'request-http.php')
	->send();

if ($response2->getBody() !== 'OK-HTTP') {
	echo sprintf('Bad response body: \'%s\', expected \'%s\'', $response2->getBody(), 'OK-HTTP') . PHP_EOL;
	exit(1);
}
if (count($response2->getHeaders()) !== 3) {
	echo 'Bad header count: ' . implode(', ', $response2->getHeaders()) . PHP_EOL;
	exit(1);
}

echo 'Response 2 is OK' . PHP_EOL;
