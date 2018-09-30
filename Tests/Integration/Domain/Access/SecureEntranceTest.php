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
use Klapuch\Encryption;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class SecureEntranceTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testSuccessfulAuthenticatingWithExactlySameCredentials(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'heslo', 'email' => 'foo@bar.cz']))->try();
		$seeker = (new Access\SecureEntrance(
			$this->connection,
			new Encryption\FakeCipher(true)
		))->enter(['email' => 'foo@bar.cz', 'password' => 'heslo']);
		Assert::same('1', $seeker->id());
	}

	public function testExitingAndBecomingToGuest(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'heslo', 'email' => 'foo@bar.cz']))->try();
		Assert::equal(
			new Access\Guest(),
			(new Access\SecureEntrance(
				$this->connection,
				new Encryption\FakeCipher(true)
			))->exit()
		);
	}

	public function testSuccessfulAuthenticatingWithCaseInsensitiveEmail(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'heslo', 'email' => 'foo@bar.cz']))->try();
		Assert::noError(function() {
			(new Access\SecureEntrance(
				$this->connection,
				new Encryption\FakeCipher(true)
			))->enter(['email' => 'FOO@bar.cz', 'password' => 'heslo']);
		});
	}

	public function testPassingWithStringObject(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'heslo', 'email' => 'foo@bar.cz']))->try();
		Assert::noError(function() {
			(new Access\SecureEntrance(
				$this->connection,
				new Encryption\FakeCipher(true)
			))->enter(
				[
					'email' => new class {
						public function __toString() {
							return 'FOO@bar.cz';
						}
					},
					'password' => new class {
						public function __toString() {
							return 'heslo';
						}
					},
				]
			);
		});
	}

	public function testAuthenticatingWithoutRehashing(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'heslo', 'email' => 'foo@bar.cz']))->try();
		Assert::same(
			'heslo',
			(new Storage\NativeQuery(
				$this->connection,
				'SELECT password FROM seekers WHERE id = ?',
				[$id]
			))->field()
		);
		$seeker = (new Access\SecureEntrance(
			$this->connection,
			new Encryption\FakeCipher(true, false)
		))->enter(['email' => 'foo@bar.cz', 'password' => 'heslo']);
		Assert::same('1', $seeker->id());
		Assert::same(
			'heslo',
			(new Storage\NativeQuery(
				$this->connection,
				'SELECT password FROM seekers WHERE id = ?',
				[$id]
			))->field()
		);
	}

	/**
	 * @throws \UnexpectedValueException Email "unknown@bar.cz" does not exist
	 */
	public function testThrowingOnAuthenticatingWithUnknownEmail(): void {
		(new Access\SecureEntrance(
			$this->connection,
			new Encryption\FakeCipher()
		))->enter(['email' => 'unknown@bar.cz', 'password' => 'heslo']);
	}

	/**
	 * @throws \UnexpectedValueException Wrong password
	 */
	public function testThrowingOnAuthenticatingWithWrongPassword(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'heslo', 'email' => 'foo@bar.cz']))->try();
		(new Access\SecureEntrance(
			$this->connection,
			new Encryption\FakeCipher(false)
		))->enter(['email' => 'foo@bar.cz', 'password' => '2heslo2']);
	}

	public function testAuthenticatingRehasingPassword(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'heslo', 'email' => 'foo@bar.cz']))->try();
		Assert::same(
			'heslo',
			(new Storage\NativeQuery(
				$this->connection,
				'SELECT password FROM seekers WHERE id = ?',
				[$id]
			))->field()
		);
		$seeker = (new Access\SecureEntrance(
			$this->connection,
			new Encryption\FakeCipher(true, true)
		))->enter(['email' => 'foo@bar.cz', 'password' => 'heslo']);
		Assert::same('1', $seeker->id());
		Assert::same(
			'secret',
			(new Storage\NativeQuery(
				$this->connection,
				'SELECT password FROM seekers WHERE id = ?',
				[$id]
			))->field()
		);
	}
}

(new SecureEntranceTest())->run();
