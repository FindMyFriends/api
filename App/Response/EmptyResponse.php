<?php
declare(strict_types = 1);

namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

final class EmptyResponse implements Application\Response {
	public function body(): Output\Format {
		return new Output\FakeFormat();
	}

	public function headers(): array {
		return [
			'Content-Type' => 'text/plain',
			'Content-Length' => 0,
		];
	}

	public function status(): int {
		return HTTP_NO_CONTENT;
	}
}