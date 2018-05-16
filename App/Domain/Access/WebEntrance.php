<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Entrance to web
 */
final class WebEntrance implements Entrance {
	private $database;

	public function __construct(Storage\MetaPDO $database) {
		$this->database = $database;
	}

	public function enter(array $credentials): Seeker {
		if (isset($credentials[self::IDENTIFIER]))
			return new RegisteredSeeker($credentials[self::IDENTIFIER], $this->database);
		return new Guest();
	}

	public function exit(): Seeker {
		return new Guest();
	}
}
