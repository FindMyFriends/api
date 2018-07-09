<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;
use Klapuch\Validation;

final class ValidReminderRule implements Validation\Rule {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(Storage\MetaPDO $database) {
		$this->database = $database;
	}

	/**
	 * @param string $subject
	 * @throws \UnexpectedValueException
	 */
	public function apply($subject): void {
		if (!$this->satisfied($subject))
			throw new \UnexpectedValueException('Reminder is no longer valid.');
	}

	/**
	 * @param string $subject
	 * @return bool
	 */
	public function satisfied($subject): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1
			FROM forgotten_passwords
			WHERE reminder IS NOT DISTINCT FROM ?
			AND used_at IS NULL
			AND expire_at > NOW()',
			[$subject]
		))->field();
	}
}
