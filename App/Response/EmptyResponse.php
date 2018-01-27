<?php
declare(strict_types = 1);

namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

final class EmptyResponse implements Application\Response {
	private $headers;

	public function __construct(array $headers = []) {
		$this->headers = $headers;
	}

	public function body(): Output\Format {
		return new Output\FakeFormat();
	}

	public function headers(): array {
		return $this->headers;
	}

	public function status(): int {
		return HTTP_NO_CONTENT;
	}
}