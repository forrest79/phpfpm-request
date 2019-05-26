<?php declare(strict_types=1);

namespace Forrest79\PhpFpmRequest;

class Response
{
	/** @var string[] */
	private $response;

	/** @var string[] */
	private $headers;

	/** @var string|NULL */
	private $body = NULL;


	public function __construct(array $response)
	{
		$this->response = $response;
	}


	public function getResponse(): string
	{
		return implode(PHP_EOL, $this->response);
	}


	public function getHeaders(): array
	{
		$this->processResponse();
		return $this->headers;
	}


	public function getBody(): ?string
	{
		$this->processResponse();
		return $this->body;
	}


	private function processResponse(): void
	{
		if ($this->headers === NULL) {
			$headers = [];
			$body = [];
			$readHeaders = TRUE;
			foreach ($this->response as $line) {
				if ($readHeaders && ($line === '')) {
					$readHeaders = FALSE;
				} else if ($readHeaders) {
					$headers[] = $line;
				} else {
					$body[] = $line;
				}
			}

			$this->headers = $headers;
			$this->body = trim(implode(PHP_EOL, $body)) ?: NULL;
		}
	}

}
