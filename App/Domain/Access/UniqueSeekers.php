<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Encryption;
use Klapuch\Storage;

/**
 * Collection of unique seekers
 */
final class UniqueSeekers implements Seekers {
	private $database;
	private $cipher;

	public function __construct(Storage\MetaPDO $database, Encryption\Cipher $cipher) {
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function register(
		string $email,
		string $password,
		string $role
	): Seeker {
		if ($this->exists($email))
			throw new \UnexpectedValueException(sprintf('Email "%s" already exists', $email));
		$row = (new Storage\TypedQuery(
			$this->database,
			'INSERT INTO seekers (email, password, role) VALUES
			(?, ?, ?)
			RETURNING *',
			[$email, $this->cipher->encryption($password), $role]
		))->row();
		return new ConstantSeeker((string) $row['id'], $row);
	}

	private function exists(string $email): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1 FROM seekers WHERE email = ?',
			[$email]
		))->field();
	}
}
