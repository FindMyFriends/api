<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Klapuch\Encryption;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class UniqueSeekers extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testRegisteringBrandNewOne() {
		$seeker = (new Access\UniqueSeekers(
			$this->database,
			new Encryption\FakeCipher()
		))->register('foo@bar.cz', 'passw0rt', 'member');
		Assert::same('1', $seeker->id());
		Assert::same(['email' => 'foo@bar.cz', 'role' => 'member'], $seeker->properties());
		$rows = (new Storage\NativeQuery($this->database, 'SELECT * FROM seekers'))->rows();
		Assert::count(1, $rows);
		Assert::same('foo@bar.cz', $rows[0]['email']);
		Assert::same('secret', $rows[0]['password']);
		Assert::same('member', $rows[0]['role']);
		Assert::same(1, $rows[0]['id']);
	}

	public function testRegisteringMultipleDifferentEmails() {
		$seekers = new Access\UniqueSeekers(
			$this->database,
			new Encryption\FakeCipher()
		);
		$seekers->register('foo@bar.cz', 'ultra secret password', 'member');
		$seekers->register('bar@foo.cz', 'weak password', 'member');
		$rows = (new Storage\NativeQuery($this->database, 'SELECT * FROM seekers'))->rows();
		Assert::count(2, $rows);
		Assert::same(1, $rows[0]['id']);
		Assert::same(2, $rows[1]['id']);
	}

	public function testThrowingOnDuplicatedEmail() {
		$register = function() {
			(new Access\UniqueSeekers(
				$this->database,
				new Encryption\FakeCipher()
			))->register('foo@bar.cz', 'password', 'member');
		};
		$register();
		Assert::exception(
			$register,
			\UnexpectedValueException::class,
			'Email "foo@bar.cz" already exists'
		);
	}

	public function testThrowingOnDuplicatedCaseInsensitiveEmail() {
		$email = 'foo@bar.cz';
		$register = function(string $email) {
			(new Access\UniqueSeekers(
				$this->database,
				new Encryption\FakeCipher()
			))->register($email, 'password', 'member');
		};
		$register($email);
		Assert::exception(
			function() use ($register, $email) {
				$register(strtoupper($email));
			},
			\UnexpectedValueException::class,
			'Email "FOO@BAR.CZ" already exists'
		);
	}
}

(new UniqueSeekers())->run();
