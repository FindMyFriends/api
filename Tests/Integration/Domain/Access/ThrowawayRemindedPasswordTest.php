<?php
declare(strict_types = 1);

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
final class ThrowawayRemindedPasswordTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	/**
	 * @throws \UnexpectedValueException The reminder is already used
	 */
	public function testThrowingOnAlreadyUsedReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->connection, 'forgotten_password', ['used_at' => date('Y-m-d'), 'reminder' => $reminder]))->try();
		(new Access\ThrowawayRemindedPassword(
			$reminder,
			$this->connection,
			new Access\FakePassword()
		))->change('123456789');
	}

	public function testUsingUnusedReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->connection, 'forgotten_password', ['used_at' => null, 'reminder' => $reminder]))->try();
		Assert::noError(function() use ($reminder) {
			(new Access\ThrowawayRemindedPassword(
				$reminder,
				$this->connection,
				new Access\FakePassword()
			))->change('123456789');
		});
	}
}

(new ThrowawayRemindedPasswordTest())->run();
