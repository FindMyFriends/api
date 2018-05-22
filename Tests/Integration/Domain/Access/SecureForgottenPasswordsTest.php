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
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class SecureForgottenPasswordsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testRemindingWithFutureExpiration() {
		(new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Access\SecureForgottenPasswords(
			$this->database
		))->remind('foo@bar.cz');
		$row = (new Storage\NativeQuery(
			$this->database,
			'SELECT * FROM forgotten_passwords'
		))->row();
		Assert::same(1, $row['seeker_id']);
		Assert::false($row['used']);
	}

	/**
	 * @throws \UnexpectedValueException The email does not exist
	 */
	public function testThrowingOnUnknownEmail() {
		(new Access\SecureForgottenPasswords(
			$this->database
		))->remind('zzz@zzz.cz');
	}

	public function testPassingWithCaseInsensitiveEmail() {
		(new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'foo@bar.cz']))->try();
		Assert::noError(function() {
			(new Access\SecureForgottenPasswords(
				$this->database
			))->remind('FOO@bar.cz');
		});
		$this->database->exec('TRUNCATE forgotten_passwords; TRUNCATE seekers CASCADE');
		(new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'bar@FOO.cz']))->try();
		Assert::noError(function() {
			(new Access\SecureForgottenPasswords(
				$this->database
			))->remind('bar@foo.cz');
		});
	}
}

(new SecureForgottenPasswordsTest())->run();
