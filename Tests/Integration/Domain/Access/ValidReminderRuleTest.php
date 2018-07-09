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

final class ValidReminderRuleTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testInvalidAsUnknownReminder() {
		$rule = new Access\ValidReminderRule($this->database);
		Assert::exception(function() use ($rule) {
			$rule->apply('123');
		}, \UnexpectedValueException::class, 'Reminder is no longer valid.');
		Assert::false($rule->satisfied('123'));
	}

	public function testInvalidAsUsedReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->database, 'forgotten_password', ['used_at' => date('Y-m-d'), 'reminder' => $reminder]))->try();
		$rule = new Access\ValidReminderRule($this->database);
		Assert::exception(function() use ($rule, $reminder) {
			$rule->apply($reminder);
		}, \UnexpectedValueException::class, 'Reminder is no longer valid.');
		Assert::false($rule->satisfied($reminder));
	}

	public function testInvalidAsExpiredReminder() {
		$this->database->exec('ALTER TABLE forgotten_passwords DROP CONSTRAINT forgotten_passwords_expire_at_future');
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData(
			$this->database,
			'forgotten_password',
			['used_at' => date('Y-m-d'), 'reminder' => $reminder, 'reminded_at' => '2004-01-01', 'expire_at' => '2005-01-01']
		))->try();
		$rule = new Access\ValidReminderRule($this->database);
		Assert::exception(function() use ($rule, $reminder) {
			$rule->apply($reminder);
		}, \UnexpectedValueException::class, 'Reminder is no longer valid.');
		Assert::false($rule->satisfied($reminder));
	}

	public function testPassingWithValidReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData(
			$this->database,
			'forgotten_password',
			[
				'used_at' => null,
				'reminder' => $reminder,
				'expire_at' => (new \DateTimeImmutable())->format('Y-m-d H:i'),
			]
		))->try();
		$rule = new Access\ValidReminderRule($this->database);
		Assert::noError(function() use ($rule, $reminder) {
			$rule->apply($reminder);
		});
		Assert::true($rule->satisfied($reminder));
	}
}

(new ValidReminderRuleTest())->run();
