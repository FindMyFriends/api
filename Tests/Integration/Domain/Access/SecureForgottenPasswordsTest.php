<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class SecureForgottenPasswordsTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testRemindingWithFutureExpiration(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Access\SecureForgottenPasswords(
			$this->connection
		))->remind('foo@bar.cz');
		$row = (new Storage\NativeQuery(
			$this->connection,
			'SELECT * FROM forgotten_passwords'
		))->row();
		Assert::same(1, $row['seeker_id']);
		Assert::null($row['used_at']);
	}

	/**
	 * @throws \UnexpectedValueException The email does not exist
	 */
	public function testThrowingOnUnknownEmail(): void {
		(new Access\SecureForgottenPasswords(
			$this->connection
		))->remind('zzz@zzz.cz');
	}

	public function testPassingWithCaseInsensitiveEmail(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@bar.cz']))->try();
		Assert::noError(function() {
			(new Access\SecureForgottenPasswords(
				$this->connection
			))->remind('FOO@bar.cz');
		});
		$this->connection->exec('TRUNCATE forgotten_passwords');
		$this->connection->exec('TRUNCATE seekers CASCADE');
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'bar@FOO.cz']))->try();
		Assert::noError(function() {
			(new Access\SecureForgottenPasswords(
				$this->connection
			))->remind('bar@foo.cz');
		});
	}
}

(new SecureForgottenPasswordsTest())->run();
