<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Entrance creating refresh tokens
 */
final class RefreshingEntrance implements Entrance {
	/**
	 * @param array $credentials
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function enter(array $credentials): Seeker {
		session_id($credentials['token']);
		session_start();
		if (!isset($_SESSION[self::IDENTIFIER])) {
			throw new \UnexpectedValueException('Provided token is not valid.');
		}
		session_regenerate_id(true);
		return new ConstantSeeker(session_id(), []);
	}

	public function exit(): Seeker {
		return new Guest();
	}
}
