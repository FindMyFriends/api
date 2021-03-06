<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class RefreshingEntranceTest extends TestCase\Runtime {
	use TestCase\RedisSession;

	public function testCreatingDifferentTokens(): void {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		(new Access\RefreshingEntrance())->enter(['token' => $id]);
		Assert::notSame($id, session_id());
	}

	public function testCopyingData(): void {
		session_start();
		$_SESSION['id'] = '1';
		$seeker = (new Access\RefreshingEntrance())->enter(['token' => session_id()]);
		Assert::same('1', $_SESSION['id']);
		Assert::equal(
			new Access\SessionSeeker(new Access\ConstantSeeker(session_id(), [])),
			$seeker
		);
	}

	public function testKeepingPrevious(): void {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		(new Access\RefreshingEntrance())->enter(['token' => $id]);
		session_write_close();
		Assert::count(2, $this->redis->keys('*'));
	}

	public function testStartingSessionOnce(): void {
		Assert::noError(static function () {
			session_start();
			$_SESSION['id'] = '1';
			(new Access\RefreshingEntrance())->enter(['token' => session_id()]);
		});
	}

	public function testThrowingOnUnknownId(): void {
		Assert::exception(static function () {
			(new Access\RefreshingEntrance())->enter(['token' => 'foo']);
		}, \UnexpectedValueException::class, 'Provided token is not valid.');
	}

	public function testThrowingOnUnknownIdWithAlreadyAssignedOne(): void {
		session_start();
		$_SESSION['id'] = '1';
		Assert::exception(static function () {
			(new Access\RefreshingEntrance())->enter(['token' => 'foo']);
		}, \UnexpectedValueException::class, 'Provided token is not valid.');
	}

	public function testRemovingUnknownToken(): void {
		session_start();
		$_SESSION['id'] = '1';
		Assert::exception(static function () {
			(new Access\RefreshingEntrance())->enter(['token' => 'foo']);
		}, \UnexpectedValueException::class, 'Provided token is not valid.');
		session_write_close();
		Assert::count(2, $this->redis->keys('*'));
	}
}

(new RefreshingEntranceTest())->run();
