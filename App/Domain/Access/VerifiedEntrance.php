<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Verified entrance
 */
final class VerifiedEntrance implements Entrance {
	private $database;
	private $origin;

	public function __construct(Storage\MetaPDO $database, Entrance $origin) {
		$this->database = $database;
		$this->origin = $origin;
	}

	public function enter(array $credentials): Seeker {
		[$email] = $credentials;
		if (!$this->verified((string) $email))
			throw new \UnexpectedValueException('Email has not been verified yet');
		return $this->origin->enter($credentials);
	}

	private function verified(string $email): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1
			FROM verification_codes  
			WHERE seeker_id = (
				SELECT id
				FROM seekers
				WHERE email IS NOT DISTINCT FROM ?
			) AND used_at IS NOT NULL',
			[$email]
		))->field();
	}

	public function exit(): Seeker {
		return $this->origin->exit();
	}
}
