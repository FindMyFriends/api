<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Works just with secure forgotten passwords
 */
final class SecureForgottenPasswords implements ForgottenPasswords {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(Storage\MetaPDO $database) {
		$this->database = $database;
	}

	public function remind(string $email): Password {
		if (!$this->exists($email))
			throw new \UnexpectedValueException('The email does not exist');
		$reminder = (new Storage\TypedQuery(
			$this->database,
			"INSERT INTO forgotten_passwords (seeker_id, reminder, reminded_at, used, expire_at) VALUES
			(?, ?, NOW(), FALSE, NOW() + INTERVAL '31 MINUTE')
			RETURNING reminder",
			[$this->id($email), bin2hex(random_bytes(50)) . ':' . sha1($email)]
		))->field();
		return new ExpirableRemindedPassword(
			$reminder,
			$this->database,
			new FakePassword()
		);
	}

	private function exists(string $email): bool {
		return (bool) $this->id($email);
	}

	/**
	 * ID matching the email, if any
	 * @param string $email
	 * @return int
	 */
	private function id(string $email): int {
		return (int) (new Storage\TypedQuery(
			$this->database,
			'SELECT id
			FROM seekers
			WHERE email IS NOT DISTINCT FROM ?',
			[$email]
		))->field();
	}
}
