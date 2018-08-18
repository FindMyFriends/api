<?php
declare(strict_types = 1);

namespace FindMyFriends\Request;

use Klapuch\Application;
use Klapuch\Output;

final class FriendlyRequest implements Application\Request {
	/** @var \Klapuch\Application\Request */
	private $origin;

	/** @var string */
	private $message;

	public function __construct(Application\Request $origin, string $message) {
		$this->origin = $origin;
		$this->message = $message;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function body(): Output\Format {
		try {
			return $this->origin->body();
		} catch (\UnexpectedValueException $e) {
			throw new \UnexpectedValueException($this->message, $e->getCode(), $e);
		}
	}

	public function headers(): array {
		return $this->origin->headers();
	}
}
