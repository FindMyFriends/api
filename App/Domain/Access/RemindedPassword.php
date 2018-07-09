<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Reminded password
 */
final class RemindedPassword implements Password {
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

	/**
	 * @param string $password
	 * @throws \UnexpectedValueException
	 */
	public function change(string $password): void {
		if (!$this->exists($this->reminder))
			throw new \UnexpectedValueException('The reminder does not exist');
		(new Storage\Transaction($this->database))->start(
			function() use ($password): void {
				$this->origin->change($password);
				(new Storage\TypedQuery(
					$this->database,
					'UPDATE forgotten_passwords
					SET used_at = now()
					WHERE reminder IS NOT DISTINCT FROM ?',
					[$this->reminder]
				))->execute();
			}
		);
	}

	private function exists(string $reminder): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1
			FROM forgotten_passwords
			WHERE reminder IS NOT DISTINCT FROM ?
			AND used_at IS NULL',
			[$reminder]
		))->field();
	}

	public function print(Output\Format $format): Output\Format {
		return $format->with('reminder', $this->reminder);
	}
}
