<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Entrance used for testing purposes
 */
final class TestingEntrance implements Entrance {
	public function enter(array $headers): Seeker {
		session_start();
		$_SESSION['id'] = '1';
		$sessionId = session_id();
		session_write_close();
		return new FakeSeeker($sessionId);
	}

	public function exit(): Seeker {
		return new Guest();
	}
}
