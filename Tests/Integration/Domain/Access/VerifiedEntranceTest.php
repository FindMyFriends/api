<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.0
 */

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class VerifiedEntranceTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	/**
	 * @throws \UnexpectedValueException Email has not been verified yet
	 */
	public function testThrowingOnNotVerifiedEmail() {
		(new Access\VerifiedEntrance(
			$this->connection,
			new Access\FakeEntrance(new Access\FakeSeeker('1'))
		))->enter(['unverified@bar.cz', 'heslo']);
	}

	public function testPassingOnVerifiedEmail() {
		['id' => $seeker] = (new Misc\SampleSeeker($this->connection, ['verification_code' => ['used_at' => 'NOW()']]))->try();
		$seeker = new Access\FakeSeeker((string) $seeker);
		Assert::same(
			$seeker,
			(new Access\VerifiedEntrance(
				$this->connection,
				new Access\FakeEntrance($seeker)
			))->enter([])
		);
	}
}

(new VerifiedEntranceTest())->run();
