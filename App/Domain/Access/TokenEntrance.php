<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Entrance creating tokens
 */
final class TokenEntrance implements Entrance {
	/** @var \FindMyFriends\Domain\Access\Entrance */
	private $origin;

	public function __construct(Entrance $origin) {
		$this->origin = $origin;
	}

	/**
	 * @param array $credentials
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function enter(array $credentials): Seeker {
		$seeker = $this->origin->enter($credentials);
		if (session_status() === PHP_SESSION_NONE)
			session_start();
		session_regenerate_id(true);
		$_SESSION[self::IDENTIFIER] = $seeker->id();
		return new ConstantSeeker(session_id(), $seeker->properties());
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function exit(): Seeker {
		if (session_status() === PHP_SESSION_ACTIVE)
			unset($_SESSION[self::IDENTIFIER]);
		return $this->origin->exit();
	}
}
