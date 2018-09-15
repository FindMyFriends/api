<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class RefreshingEntranceTest extends Tester\TestCase {
	public function testCreatingDifferentTokens() {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		session_write_close();
		(new Access\RefreshingEntrance())->enter(['token' => $id]);
		Assert::notSame($id, session_id());
	}

	public function testCopyingData() {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		session_write_close();
		$seeker = (new Access\RefreshingEntrance())->enter(['token' => $id]);
		Assert::same('1', $_SESSION['id']);
		Assert::equal(
			new Access\SessionSeeker(new Access\ConstantSeeker(session_id(), [])),
			$seeker
		);
	}

	public function testRemovingPrevious() {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		session_write_close();
		(new Access\RefreshingEntrance())->enter(['token' => $id]);
		session_write_close();
		session_id($id);
		session_start();
		$_SESSION['id'] = 'foo';
		Assert::same(['id' => 'foo'], $_SESSION);
	}

	/**
	 * @throws \UnexpectedValueException Provided token is not valid.
	 */
	public function testThrowingOnUnknownId() {
		(new Access\RefreshingEntrance())->enter(['token' => 'foo']);
	}
}

(new RefreshingEntranceTest())->run();
