<?php declare(strict_types=1);

namespace Forrest79\PhpFpmRequest;

class Response
{
	/** @var string */
	private $response = '';

	/** @var string[] */
	private $headers = [];

	/** @var string|NULL */
	private $body = NULL;


	public function __construct(string $response)
	{
		$this->response = $response;
	}


	public function getResponse(): string
	{
		return $this->response;
	}


	public function getHeaders(): array
	{
		$this->processResponse();
		return $this->headers;
	}


	public function getBody(): string
	{
		$this->processResponse();
		return $this->body;
	}


	private function processResponse(): void
	{
		if ($this->body === NULL) {
			$delimiter = strpos($this->response, "\r\n") !== FALSE ? "\r\n" : "\n";

			$data = explode($delimiter . $delimiter, $this->response);
			$this->body = trim(array_pop($data));
			$this->headers = explode($delimiter, implode($delimiter, $data));
		}
	}

}
