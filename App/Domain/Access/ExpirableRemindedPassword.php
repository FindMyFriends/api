<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Reminded password with expiration
 */
final class ExpirableRemindedPassword implements Password {
	/** @var string */
	private $reminder;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Password */
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
		if ($this->expired($this->reminder))
			throw new \UnexpectedValueException('The reminder expired');
		$this->origin->change($password);
	}

	private function expired(string $reminder): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1
			FROM forgotten_passwords
			WHERE reminder IS NOT DISTINCT FROM ?
			AND expire_at < NOW()',
			[$reminder]
		))->field();
	}

	public function print(Output\Format $format): Output\Format {
		return $format->with('reminder', $this->reminder)
			->with('expiration', $this->expiration($this->reminder));
	}

	private function expiration(string $reminder): string {
		return (new Storage\TypedQuery(
			$this->database,
			"SELECT EXTRACT(MINUTE FROM expire_at - NOW()) || ' minutes'
			FROM forgotten_passwords
			WHERE reminder IS NOT DISTINCT FROM ?",
			[$reminder]
		))->field();
	}
}
