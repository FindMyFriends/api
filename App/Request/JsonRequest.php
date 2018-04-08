<?php
declare(strict_types = 1);

namespace FindMyFriends\Request;

use Klapuch\Application;
use Klapuch\Output;

final class JsonRequest implements Application\Request {
	private $origin;

	public function __construct(Application\Request $origin) {
		$this->origin = $origin;
	}

	public function body(): Output\Format {
		return new Output\Json(
			json_decode(
				$this->origin->body()->serialization(),
				true
			)
		);
	}

	public function headers(): array {
		return $this->origin->headers();
	}
}
