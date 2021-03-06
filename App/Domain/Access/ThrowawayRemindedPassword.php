<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Reminded password just for one use
 */
final class ThrowawayRemindedPassword implements Password {
	/** @var string */
	private $reminder;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Password */
	private $origin;

	public function __construct(
		string $reminder,
		Storage\Connection $connection,
		Password $origin
	) {
		$this->reminder = $reminder;
		$this->connection = $connection;
		$this->origin = $origin;
	}

	/**
	 * @param string $password
	 * @throws \UnexpectedValueException
	 */
	public function change(string $password): void {
		if ($this->used($this->reminder))
			throw new \UnexpectedValueException('The reminder is already used');
		$this->origin->change($password);
	}

	private function used(string $reminder): bool {
		return (bool) (new Storage\TypedQuery(
			$this->connection,
			'SELECT 1
			FROM forgotten_passwords
			WHERE reminder IS NOT DISTINCT FROM ?
			AND used_at IS NOT NULL',
			[$reminder]
		))->field();
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format);
	}
}
