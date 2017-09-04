<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

/**
 * Error for JSON format
 */
final class JsonError implements Application\Response {
	private const HEADERS = ['content-type' => 'application/json; charset=utf8'];
	private const CODES = [400, 599],
		DEFAULT_CODE = 400,
		DELEGATE = 0;
	private $error;
	private $headers;
	private $code;

	public function __construct(
		\Throwable $error,
		array $headers = [],
		int $code = self::DELEGATE
	) {
		$this->error = $error;
		$this->headers = $headers;
		$this->code = $code;
	}

	public function body(): Output\Format {
		return new Output\Json(['message' => $this->text($this->error)]);
	}

	public function headers(): array {
		http_response_code($this->code($this->error, $this->code));
		return self::HEADERS + array_change_key_case($this->headers);
	}

	private function code(\Throwable $error, int $code): int {
		$choice = $error->getCode() ?: $code;
		return in_array($choice, range(...self::CODES))
			? $choice
			: self::DEFAULT_CODE;
	}

	private function text(\Throwable $error): string {
		return htmlspecialchars($error->getMessage(), ENT_QUOTES | ENT_XHTML)
			?: 'Unknown error, contact support.';
	}
}