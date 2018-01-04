<?php
declare(strict_types = 1);

namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

/**
 * JSON response
 */
final class JsonResponse implements Application\Response {
	private const HEADERS = ['Content-Type' => 'application/json; charset=utf8'];
	private $origin;

	public function __construct(Application\Response $origin) {
		$this->origin = $origin;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		return self::HEADERS + $this->origin->headers();
	}

	public function status(): int {
		return $this->origin->status();
	}
}