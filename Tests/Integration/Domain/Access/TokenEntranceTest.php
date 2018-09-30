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
final class TokenEntranceTest extends TestCase\Runtime {
	public function testRetrievedSeekerSessionIdOnEntering(): void {
		Assert::match(
			'~^[\w\d,-]{60}$~',
			(new Access\TokenEntrance(
				new Access\FakeEntrance(new Access\FakeSeeker('1', []))
			))->enter([])->id()
		);
	}

	public function testEnteringWithSetSession(): void {
		(new Access\TokenEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker('1', []))
		))->enter([]);
		Assert::same('1', $_SESSION['id']);
	}

	public function testNewIdOnEachEntering(): void {
		$entrance = new Access\TokenEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker('1', []))
		);
		Assert::notSame($entrance->enter([])->id(), $entrance->enter([])->id());
	}

	public function testExitingWithDelegation(): void {
		$seeker = new Access\FakeSeeker('1');
		Assert::same(
			$seeker,
			(new Access\TokenEntrance(new Access\FakeEntrance($seeker)))->exit()
		);
	}

	public function testDeletingSessionOnExit(): void {
		session_start();
		session_regenerate_id(true);
		$_SESSION['id'] = '1';
		Assert::true(isset($_SESSION['id']));
		(new Access\TokenEntrance(new Access\FakeEntrance(new Access\FakeSeeker())))->exit();
		Assert::false(isset($_SESSION['id']));
	}
}

(new TokenEntranceTest())->run();
