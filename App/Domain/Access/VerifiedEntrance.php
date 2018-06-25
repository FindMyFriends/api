<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Verified entrance
 */
final class VerifiedEntrance implements Entrance {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Entrance */
	private $origin;

	public function __construct(Storage\MetaPDO $database, Entrance $origin) {
		$this->database = $database;
		$this->origin = $origin;
	}

	/**
	 * @param array $credentials
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function enter(array $credentials): Seeker {
		$seeker = $this->origin->enter($credentials);
		if (!$this->verified($seeker))
			throw new \UnexpectedValueException('Email has not been verified yet');
		return $seeker;
	}

	private function verified(Seeker $seeker): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1
			FROM verification_codes  
			WHERE seeker_id = ?
			AND used_at IS NOT NULL',
			[$seeker->id()]
		))->field();
	}

	/**
	 * @return \FindMyFriends\Domain\Access\Seeker
	 * @throws \UnexpectedValueException
	 */
	public function exit(): Seeker {
		return $this->origin->exit();
	}
}
