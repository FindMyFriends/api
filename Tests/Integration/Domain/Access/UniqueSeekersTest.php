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

final class UniqueSeekersTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testCreatedSeekerWithBaseEvolution() {
		$seeker = (new Access\UniqueSeekers(
			$this->database,
			new Encryption\FakeCipher()
		))->join([
			'email' => 'foo@bar.cz',
			'password' => 'passw0rt',
			'general' => [
				'birth_year' => 1996,
				'sex' => 'man',
				'ethnic_group_id' => 2,
				'firstname' => 'Dominik',
				'lastname' => 'Klapuch',
			],
		]);
		Assert::same('1', $seeker->id());
		Assert::same(['email' => 'foo@bar.cz', 'role' => 'member'], $seeker->properties());
		$seekers = (new Storage\NativeQuery($this->database, 'SELECT * FROM seekers'))->rows();
		$evolutions = (new Storage\NativeQuery($this->database, 'SELECT * FROM evolutions'))->rows();
		$general = (new Storage\NativeQuery($this->database, 'SELECT * FROM general'))->rows();
		Assert::count(1, $seekers);
		Assert::count(1, $evolutions);
		Assert::count(1, $general);
		Assert::same('foo@bar.cz', $seekers[0]['email']);
		Assert::same('secret', $seekers[0]['password']);
		Assert::same('member', $seekers[0]['role']);
		Assert::same(1, $seekers[0]['id']);
		Assert::same('[1996,1997)', $general[0]['birth_year']);
	}

	public function testJoiningMultipleDifferentEmails() {
		$seekers = new Access\UniqueSeekers(
			$this->database,
			new Encryption\FakeCipher()
		);
		$seekers->join([
			'email' => 'foo@bar.cz',
			'password' => 'ultra secret password',
			'general' => [
				'birth_year' => 1996,
				'sex' => 'man',
				'ethnic_group_id' => 2,
				'firstname' => 'Dominik',
				'lastname' => 'Klapuch',
			],
		]);
		$seekers->join([
			'email' => 'bar@foo.cz',
			'password' => 'weak password',
			'general' => [
				'birth_year' => 1996,
				'sex' => 'man',
				'ethnic_group_id' => 2,
				'firstname' => 'Dominik',
				'lastname' => 'Klapuch',
			],
		]);
		$rows = (new Storage\NativeQuery($this->database, 'SELECT * FROM seekers'))->rows();
		$evolutions = (new Storage\NativeQuery($this->database, 'SELECT * FROM evolutions'))->rows();
		Assert::count(2, $rows);
		Assert::count(2, $evolutions);
		Assert::same(1, $rows[0]['id']);
		Assert::same(2, $rows[1]['id']);
	}

	public function testThrowingOnDuplicatedEmail() {
		$register = function() {
			(new Access\UniqueSeekers(
				$this->database,
				new Encryption\FakeCipher()
			))->join([
				'email' => 'foo@bar.cz',
				'password' => 'password',
				'general' => [
					'birth_year' => 1996,
					'sex' => 'man',
					'ethnic_group_id' => 2,
					'firstname' => 'Dominik',
					'lastname' => 'Klapuch',
				],
			]);
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
			))->join([
				'email' => $email,
				'password' => 'password',
				'general' => [
					'birth_year' => 1996,
					'sex' => 'man',
					'ethnic_group_id' => 2,
					'firstname' => 'Dominik',
					'lastname' => 'Klapuch',
				],
			]);
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

(new UniqueSeekersTest())->run();
