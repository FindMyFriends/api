<?php
declare(strict_types = 1);

/**
 * @phpIni session.save_handler=files
 * @phpIni session.save_path=
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class RefreshingEntranceTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testCreatingDifferentTokens() {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		session_write_close();
		(new Access\RefreshingEntrance($this->database))->enter(['token' => $id]);
		Assert::notSame($id, session_id());
	}

	public function testCopyingData() {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		session_write_close();
		$seeker = (new Access\RefreshingEntrance($this->database))->enter(['token' => $id]);
		Assert::same('1', $_SESSION['id']);
		Assert::equal(new Access\RegisteredSeeker('1', $this->database), $seeker);
	}

	public function testRemovingPrevious() {
		session_start();
		$_SESSION['id'] = '1';
		$id = session_id();
		session_write_close();
		(new Access\RefreshingEntrance($this->database))->enter(['token' => $id]);
		session_write_close();
		session_id($id);
		session_start();
		Assert::same([], $_SESSION);
	}

	/**
	 * @throws \UnexpectedValueException Provided token is not valid.
	 */
	public function testThrowingOnUnknownId() {
		(new Access\RefreshingEntrance($this->database))->enter(['token' => 'foo']);
	}
}

(new RefreshingEntranceTest())->run();
