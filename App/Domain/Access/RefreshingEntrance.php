<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Entrance creating refresh tokens
 */
final class RefreshingEntrance implements Entrance {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(Storage\MetaPDO $database) {
		$this->database = $database;
	}

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
		return new RegisteredSeeker($_SESSION[self::IDENTIFIER], $this->database);
	}

	public function exit(): Seeker {
		return new Guest();
	}
}
