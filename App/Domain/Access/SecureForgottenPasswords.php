<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Works just with secure forgotten passwords
 */
final class SecureForgottenPasswords implements ForgottenPasswords {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	/**
	 * @param string $email
	 * @return \FindMyFriends\Domain\Access\Password
	 * @throws \UnexpectedValueException
	 */
	public function remind(string $email): Password {
		if (!$this->exists($email))
			throw new \UnexpectedValueException('The email does not exist');
		$reminder = (new Storage\TypedQuery(
			$this->connection,
			"INSERT INTO forgotten_passwords (seeker_id, reminder, reminded_at, expire_at) VALUES
			(?, ?, NOW(), NOW() + INTERVAL '31 MINUTE')
			RETURNING reminder",
			[$this->id($email), bin2hex(random_bytes(50)) . ':' . sha1($email)]
		))->field();
		return new ExpirableRemindedPassword(
			$reminder,
			$this->connection,
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
			$this->connection,
			'SELECT id
			FROM seekers
			WHERE email IS NOT DISTINCT FROM ?',
			[$email]
		))->field();
	}
}
