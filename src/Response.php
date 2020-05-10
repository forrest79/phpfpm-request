<?php declare(strict_types=1);

namespace Forrest79\PhpFpmRequest;

class Response
{
	/** @var array<string> */
	private array $response;

	/** @var array<string> */
	private ?array $headers = NULL;

	private ?string $body = NULL;


	/**
	 * @param array<string> $response
	 */
	public function __construct(array $response)
	{
		$this->response = $response;
	}


	public function getResponse(): string
	{
		return implode(PHP_EOL, $this->response);
	}


	/**
	 * @return array<string>
	 */
	public function getHeaders(): array
	{
		$this->processResponse();
		assert(is_array($this->headers));
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

			$trimmedBody = trim(implode(PHP_EOL, $body));

			$this->headers = $headers;
			$this->body = $trimmedBody === '' ? NULL : $trimmedBody;
		}
	}

}
