<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Entrance used for testing purposes
 */
final class TestingEntrance implements Entrance {
	public function enter(array $credentials): Seeker {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		session_write_close();
		return new FakeSeeker($id);
	}

	public function exit(): Seeker {
		return new Guest();
	}
}
