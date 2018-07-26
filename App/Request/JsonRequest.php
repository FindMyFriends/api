<?php
declare(strict_types = 1);

namespace FindMyFriends\Request;

use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Output;

final class JsonRequest implements Application\Request {
	/** @var \Klapuch\Application\Request */
	private $origin;

	public function __construct(Application\Request $origin) {
		$this->origin = $origin;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function body(): Output\Format {
		return new Output\Json(
			(new Internal\DecodedJson(
				$this->origin->body()->serialization()
			))->values()
		);
	}

	public function headers(): array {
		return $this->origin->headers();
	}
}
