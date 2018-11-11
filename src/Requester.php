<?php declare(strict_types=1);

namespace Forrest79\PhpFpmRequest;

class Requester
{
	public const PHP72_SOCK = '/var/run/php/php7.2-fpm.sock';
	public const PHP71_SOCK = '/var/run/php/php7.1-fpm.sock';
	public const TCP_IP = '127.0.0.1:9000';

	private const LISTERNERS = [
		self::PHP72_SOCK,
		self::PHP71_SOCK,
		self::TCP_IP,
	];

	/** @var string */
	private $listener;

	/** @var array */
	private $options;


	public function __construct(?string $listener = NULL)
	{
		$this->listener = $listener;
		$this->setMethod('GET');
	}


	public function setMethod(string $method): self
	{
		return $this->setOption('REQUEST_METHOD', $method);
	}


	public function setPhpFile(string $path): self
	{
		return $this->setOption('SCRIPT_FILENAME', $path);
	}


	public function setOption(string $name, string $value): self
	{
		$this->options[$name] = $value;
		return $this;
	}


	public function send(): Response
	{
		$options = [];
		foreach ($this->options as $name => $value) {
			$options[] = sprintf('%s=%s', $name, $value);
		}
		$options[] = 'cgi-fcgi -bind -connect "%s" 2>&1';
		$command = sprintf(implode(' \\' . PHP_EOL, $options), $this->listener);
		exec($command, $output, $exitCode);

		$returned = implode(PHP_EOL, $output);
		if ($exitCode === 0) {
			return new Response($returned);
		} else {
			if (strpos($returned, 'cgi-fcgi: not found') !== FALSE) {
				throw new Exceptions\CgiFcgiNotFoundException('\'cgi-fcgi\' utility not found in your system. In Debian/Ubuntu try install it with \'sudo apt-get install libfcgi0ldbl\'.');
			} else {
				throw new Exceptions\CgiFcgiException($returned, $exitCode);
			}
		}
	}


	public static function create(string $listener): self
	{
		return new static($listener);
	}


	public static function autodetect(): self
	{
		foreach (self::LISTERNERS as $listener) {
			if (self::isListening($listener) === TRUE) {
				return self::create($listener);
			}
		}

		throw new Exceptions\NoListenerDetectedException();
	}


	private static function isListening(string $listener): bool
	{
		if (strpos($listener, ':') === FALSE) { // socket
			return file_exists($listener);
		} else { // TCP/IP
			if ((string) (int) $listener === $listener) { // only port
				$ip = '127.0.0.1';
				$port = $listener;
			} else {
				[$ip, $port] = explode(':', $listener);
			}

			$fp = @fsockopen($ip, (int) $port, $errno, $errstr, 0.1);
			if (!$fp) {
				return FALSE;
			} else {
				fclose($fp);
				return TRUE;
			}
		}
	}

}
