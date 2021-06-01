<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Forrest79\PhpFpmRequest;

echo 'Request 1: ';

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

echo 'response is OK' . PHP_EOL;

// ---

echo 'Request 2: ';

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

echo 'response is OK' . PHP_EOL;

// ---

echo 'Request 3: ';

$exceptionWasThrown = FALSE;
try {
	PhpFpmRequest\Requester::autodetect()
		->setPhpFile(__DIR__ . DIRECTORY_SEPARATOR . 'non-existing-request.php');
} catch (PhpFpmRequest\Exceptions\PhpFileNotFoundException $e) {
	$exceptionWasThrown = TRUE;
}

if (!$exceptionWasThrown) {
	echo 'PhpFileNotFoundException was expected but not thrown.' . PHP_EOL;
	exit(1);
}

echo 'exception was successfully thrown' . PHP_EOL;
