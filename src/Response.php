<?php declare(strict_types=1);

namespace Forrest79\PhpFpmRequest;

class Response
{
	/** @var list<string> */
	private array $response;

	/** @var list<string>|null */
	private array|null $headers = null;

	private string|null $body = null;


	/**
	 * @param list<string> $response
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
	 * @return list<string>
	 */
	public function getHeaders(): array
	{
		$this->processResponse();
		assert(is_array($this->headers));
		return $this->headers;
	}


	public function getBody(): string|null
	{
		$this->processResponse();
		return $this->body;
	}


	private function processResponse(): void
	{
		if ($this->headers === null) {
			$headers = [];
			$body = [];
			$readHeaders = true;
			foreach ($this->response as $line) {
				if ($readHeaders && ($line === '')) {
					$readHeaders = false;
				} else if ($readHeaders) {
					$headers[] = $line;
				} else {
					$body[] = $line;
				}
			}

			$trimmedBody = trim(implode(PHP_EOL, $body));

			$this->headers = $headers;
			$this->body = $trimmedBody === '' ? null : $trimmedBody;
		}
	}

}
