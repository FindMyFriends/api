<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Reminded password just for one use
 */
final class ThrowawayRemindedPassword implements Password {
	private $reminder;
	private $database;
	private $origin;

	public function __construct(
		string $reminder,
		Storage\MetaPDO $database,
		Password $origin
	) {
		$this->reminder = $reminder;
		$this->database = $database;
		$this->origin = $origin;
	}

	public function change(string $password): void {
		if ($this->used($this->reminder))
			throw new \UnexpectedValueException('The reminder is already used');
		$this->origin->change($password);
	}

	private function used(string $reminder): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1
			FROM forgotten_passwords
			WHERE reminder IS NOT DISTINCT FROM ?
			AND used = TRUE',
			[$reminder]
		))->field();
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format);
	}
}