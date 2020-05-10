<?php declare(strict_types=1);

namespace Forrest79\PhpFpmRequest;

class Requester
{
	public const PHP74_SOCK = '/var/run/php/php7.4-fpm.sock';
	public const TCP_IP = '127.0.0.1:9000';

	private const LISTENERS = [
		self::PHP74_SOCK,
		self::TCP_IP,
	];

	private ?string $listener;

	/** @var array<string, mixed> */
	private array $options = [];

	private static ?string $detectedListener = NULL;


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

		if ($exitCode === 0) {
			return new Response($output);
		} else {
			$returned = implode(PHP_EOL, $output);
			if (strpos($returned, 'cgi-fcgi: not found') !== FALSE) {
				throw new Exceptions\CgiFcgiNotFoundException('\'cgi-fcgi\' utility not found in your system. In Debian/Ubuntu try install it with \'sudo apt-get install libfcgi0ldbl\'.');
			} else {
				throw new Exceptions\CgiFcgiException($returned, $exitCode);
			}
		}
	}


	public static function create(string $listener): self
	{
		return new self($listener);
	}


	public static function autodetect(): self
	{
		if (self::$detectedListener === NULL) {
			foreach (self::LISTENERS as $listener) {
				if (self::isListening($listener) === TRUE) {
					self::$detectedListener = $listener;
					break;
				}
			}

			if (self::$detectedListener === NULL) {
				throw new Exceptions\NoListenerDetectedException();
			}
		}

		return self::create(self::$detectedListener);
	}


	private static function isListening(string $listener): bool
	{
		if ((string) (int) $listener === $listener) { // only port
			$listener = '127.0.0.1:' . $listener;
		}

		if (strpos($listener, ':') === FALSE) { // socket
			return file_exists($listener);
		} else { // TCP/IP
			[$ip, $port] = explode(':', $listener);

			$fp = @fsockopen($ip, (int) $port, $errno, $errstr, 0.1); // intentionally @
			if ($fp === FALSE) {
				return FALSE;
			} else {
				fclose($fp);
				return TRUE;
			}
		}
	}

}
