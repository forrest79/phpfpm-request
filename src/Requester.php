<?php declare(strict_types=1);

namespace Forrest79\PhpFpmRequest;

class Requester
{
	public const PHP84_SOCK = '/var/run/php/php8.4-fpm.sock';
	public const PHP83_SOCK = '/var/run/php/php8.3-fpm.sock';
	public const PHP82_SOCK = '/var/run/php/php8.2-fpm.sock';
	public const PHP81_SOCK = '/var/run/php/php8.1-fpm.sock';
	public const PHP80_SOCK = '/var/run/php/php8.0-fpm.sock';
	public const TCP_IP = '127.0.0.1:9000';

	private const LISTENERS = [
		self::PHP84_SOCK,
		self::PHP83_SOCK,
		self::PHP82_SOCK,
		self::PHP81_SOCK,
		self::PHP80_SOCK,
		self::TCP_IP,
	];

	private string|NULL $listener;

	/** @var array<string, string> */
	private array $options = [];

	private static string|NULL $detectedListener = NULL;


	public function __construct(string|NULL $listener = NULL)
	{
		$this->listener = $listener;
		$this->setMethod('GET');
	}


	public function setPhpFile(string $path): self
	{
		$realpath = realpath($path);
		if (($realpath === FALSE) || !is_file($realpath)) {
			throw new Exceptions\PhpFileNotFoundException(sprintf('PHP file to request \'%s\' not found.', $path));
		}

		return $this->setOption('SCRIPT_FILENAME', $realpath);
	}


	public function setMethod(string $method): self
	{
		return $this->setOption('REQUEST_METHOD', $method);
	}


	/**
	 * @param array<string, mixed> $parameters
	 */
	public function setQuery(array $parameters): self
	{
		return $this->setOption('QUERY_STRING', http_build_query($parameters));
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
			$options[] = sprintf('%s=%s', $name, str_replace('%', '%%', $value)); // str_replace - escaping for sprintf
		}
		$options[] = 'cgi-fcgi -bind -connect "%s" 2>&1';
		$command = sprintf(implode(' \\' . PHP_EOL, $options), $this->listener);
		exec($command, $output, $exitCode);

		if ($exitCode === 0) {
			return new Response($output);
		} else {
			$returned = implode(PHP_EOL, $output);
			if (str_contains($returned, 'cgi-fcgi: not found')) {
				throw new Exceptions\CgiFcgiNotFoundException('\'cgi-fcgi\' utility not found in your system. In Debian/Ubuntu try install it with \'sudo apt install libfcgi0ldbl\'.');
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

		if (!str_contains($listener, ':')) { // socket
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
