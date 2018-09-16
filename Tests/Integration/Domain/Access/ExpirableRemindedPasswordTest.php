<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class ExpirableRemindedPasswordTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	/**
	 * @throws \UnexpectedValueException The reminder expired
	 */
	public function testThrowingOnOldReminder() {
		$this->database->exec('ALTER TABLE forgotten_passwords DROP CONSTRAINT forgotten_passwords_expire_at_future');
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData(
			$this->database,
			'forgotten_password',
			[
				'reminder' => $reminder,
				'used' => false,
				'reminded_at' => (new \DateTimeImmutable('-3 hour'))->format('Y-m-d H:i:s'),
				'expire_at' => (new \DateTimeImmutable('-2 hour'))->format('Y-m-d H:i:s'),
			]
		))->try();
		(new Access\ExpirableRemindedPassword(
			$reminder,
			$this->database,
			new Access\FakePassword()
		))->change('123456789');
	}

	public function testChangingPasswordWithFreshReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData(
			$this->database,
			'forgotten_password',
			['reminder' => $reminder, 'used' => false, 'expire_at' => (new \DateTimeImmutable('+10 minutes'))->format('Y-m-d H:i:s')]
		))->try();
		Assert::noError(function() use ($reminder) {
			(new Access\ExpirableRemindedPassword(
				$reminder,
				$this->database,
				new Access\FakePassword()
			))->change('123456789');
		});
	}

	public function testPrintingWithExpirationTime() {
		$reminder = str_repeat('x', 141);
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Misc\SamplePostgresData(
			$this->database,
			'forgotten_password',
			['seeker_id' => $seeker, 'reminder' => $reminder, 'expire_at' => (new \DateTimeImmutable('+31 minutes'))->format('Y-m-d H:i:s')]
		))->try();
		Assert::same(
			sprintf('|reminder|%s||expiration|30 minutes|', $reminder),
			(new Access\ExpirableRemindedPassword(
				$reminder,
				$this->database,
				new Access\FakePassword(new Output\FakeFormat('|abc||def|'))
			))->print(new Output\FakeFormat(''))->serialization()
		);
	}
}

(new ExpirableRemindedPasswordTest())->run();
