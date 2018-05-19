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
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class RemindedPasswordTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testChangingWithValidReminder() {
		$this->database->exec('ALTER TABLE forgotten_passwords DROP CONSTRAINT forgotten_passwords_expire_at_future');
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->database, 'forgotten_password', ['used' => false, 'reminder' => $reminder]))->try();
		$password = \Mockery::mock(Access\Password::class);
		$password->shouldReceive('change')->once()->with('123456789');
		(new Access\RemindedPassword(
			$reminder,
			$this->database,
			$password
		))->change('123456789');
		Assert::true((new Storage\NativeQuery($this->database, 'SELECT used FROM forgotten_passwords'))->field());
		\Mockery::close();
	}

	/**
	 * @throws \UnexpectedValueException The reminder does not exist
	 */
	public function testThrowingOnChangingWithUnknownReminder() {
		(new Access\RemindedPassword(
			'unknown:reminder',
			$this->database,
			new Access\FakePassword()
		))->change('123456789');
	}

	/**
	 * @throws \UnexpectedValueException The reminder does not exist
	 */
	public function testThrowingOnChangingWithUsedReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->database, 'forgotten_password', ['used' => true, 'reminder' => $reminder]))->try();
		(new Access\RemindedPassword(
			$reminder,
			$this->database,
			new Access\FakePassword()
		))->change('new password');
	}

	/**
	 * @throws \UnexpectedValueException The reminder does not exist
	 */
	public function testThrowingOnUsingCaseInsensitiveReminder() {
		$reminder = str_repeat('x', 141);
		(new Misc\SamplePostgresData($this->database, 'forgotten_password', ['used' => false, 'reminder' => $reminder]))->try();
		(new Access\RemindedPassword(
			strtoupper($reminder),
			$this->database,
			new Access\FakePassword()
		))->change('123456789');
	}

	public function testPrinting() {
		Assert::same(
			'|reminder|123reminder123|',
			(new Access\RemindedPassword(
				'123reminder123',
				$this->database,
				new Access\FakePassword()
			))->print(new Output\FakeFormat(''))->serialization()
		);
	}
}

(new RemindedPasswordTest())->run();
