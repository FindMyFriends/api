<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ValidReminderRuleTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testInvalidAsUnknownReminder(): void {
		$rule = new Access\ValidReminderRule($this->connection);
		Assert::exception(static function() use ($rule) {
			$rule->apply('123');
		}, \UnexpectedValueException::class, 'Reminder is no longer valid.');
		Assert::false($rule->satisfied('123'));
	}

	public function testInvalidAsUsedReminder(): void {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->connection, 'forgotten_password', ['used_at' => date('Y-m-d'), 'reminder' => $reminder]))->try();
		$rule = new Access\ValidReminderRule($this->connection);
		Assert::exception(static function() use ($rule, $reminder) {
			$rule->apply($reminder);
		}, \UnexpectedValueException::class, 'Reminder is no longer valid.');
		Assert::false($rule->satisfied($reminder));
	}

	public function testInvalidAsExpiredReminder(): void {
		$this->connection->exec('ALTER TABLE forgotten_passwords DROP CONSTRAINT forgotten_passwords_expire_at_future');
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData(
			$this->connection,
			'forgotten_password',
			['used_at' => date('Y-m-d'), 'reminder' => $reminder, 'reminded_at' => '2004-01-01', 'expire_at' => '2005-01-01']
		))->try();
		$rule = new Access\ValidReminderRule($this->connection);
		Assert::exception(static function() use ($rule, $reminder) {
			$rule->apply($reminder);
		}, \UnexpectedValueException::class, 'Reminder is no longer valid.');
		Assert::false($rule->satisfied($reminder));
	}

	public function testPassingWithValidReminder(): void {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData(
			$this->connection,
			'forgotten_password',
			[
				'used_at' => null,
				'reminder' => $reminder,
				'expire_at' => (new \DateTimeImmutable())->format('Y-m-d H:i'),
			]
		))->try();
		$rule = new Access\ValidReminderRule($this->connection);
		Assert::noError(static function() use ($rule, $reminder) {
			$rule->apply($reminder);
		});
		Assert::true($rule->satisfied($reminder));
	}
}

(new ValidReminderRuleTest())->run();
