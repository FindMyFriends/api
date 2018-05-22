<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ThrowawayRemindedPasswordTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	/**
	 * @throws \UnexpectedValueException The reminder is already used
	 */
	public function testThrowingOnAlreadyUsedReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->database, 'forgotten_password', ['used' => true, 'reminder' => $reminder]))->try();
		(new Access\ThrowawayRemindedPassword(
			$reminder,
			$this->database,
			new Access\FakePassword()
		))->change('123456789');
	}

	public function testUsingUnusedReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->database, 'forgotten_password', ['used' => false, 'reminder' => $reminder]))->try();
		Assert::noError(function() use ($reminder) {
			(new Access\ThrowawayRemindedPassword(
				$reminder,
				$this->database,
				new Access\FakePassword()
			))->change('123456789');
		});
	}
}

(new ThrowawayRemindedPasswordTest())->run();
