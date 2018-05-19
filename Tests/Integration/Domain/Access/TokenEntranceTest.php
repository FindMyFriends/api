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

final class TokenEntranceTest extends Tester\TestCase {
	public function testRetrievedSeekerSessionIdOnEntering() {
		Assert::match(
			'~^[\w\d,-]{60}$~',
			(new Access\TokenEntrance(
				new Access\FakeEntrance(new Access\FakeSeeker('1', []))
			))->enter([])->id()
		);
	}

	public function testEnteringWithSetSession() {
		(new Access\TokenEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker('1', []))
		))->enter([]);
		Assert::same('1', $_SESSION['id']);
	}

	public function testNewIdOnEachEntering() {
		$entrance = new Access\TokenEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker('1', []))
		);
		Assert::notSame($entrance->enter([])->id(), $entrance->enter([])->id());
	}

	public function testExitingWithDelegation() {
		$seeker = new Access\FakeSeeker('1');
		Assert::same(
			$seeker,
			(new Access\TokenEntrance(new Access\FakeEntrance($seeker)))->exit()
		);
	}
}

(new TokenEntranceTest())->run();
