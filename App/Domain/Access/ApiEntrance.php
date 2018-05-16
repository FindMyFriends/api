<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Entrance to API with valid token
 */
final class ApiEntrance implements Entrance {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function enter(array $headers): Seeker {
		if ($this->authorized($headers))
			return new RegisteredSeeker($_SESSION[self::IDENTIFIER], $this->database);
		return new Guest();
	}

	private function authorized(array $headers): bool {
		if ((bool) preg_match('~[\w\d-,]{22,256}~', $this->token($headers))) {
			session_id($this->token($headers));
			if (session_status() === PHP_SESSION_NONE)
				session_start();
			return isset($_SESSION[self::IDENTIFIER]);
		}
		return false;
	}

	private function token(array $headers): string {
		return explode(' ', $headers['authorization'] ?? '', 2)[1] ?? '';
	}

	public function exit(): Seeker {
		return new Guest();
	}
}
