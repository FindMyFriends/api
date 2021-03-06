<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Klapuch\Encryption;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class UniqueSeekersTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testCreatedSeekerWithBaseEvolution(): void {
		$seeker = (new Access\UniqueSeekers(
			$this->connection,
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
			'contact' => [
				'facebook' => 'klapuchdominik',
				'instagram' => null,
				'phone_number' => null,
			],
		]);
		Assert::same('1', $seeker->id());
		Assert::same(['email' => 'foo@bar.cz', 'role' => 'member'], $seeker->properties());
		$seekers = (new Storage\NativeQuery($this->connection, 'SELECT * FROM seekers'))->rows();
		$evolutions = (new Storage\NativeQuery($this->connection, 'SELECT * FROM evolutions'))->rows();
		$general = (new Storage\NativeQuery($this->connection, 'SELECT * FROM general'))->rows();
		Assert::count(1, $seekers);
		Assert::count(1, $evolutions);
		Assert::count(1, $general);
		Assert::same('foo@bar.cz', $seekers[0]['email']);
		Assert::same('secret', $seekers[0]['password']);
		Assert::same('member', $seekers[0]['role']);
		Assert::same(1, $seekers[0]['id']);
		Assert::same(1996, $general[0]['birth_year']);
	}

	public function testJoiningMultipleDifferentEmails(): void {
		$seekers = new Access\UniqueSeekers(
			$this->connection,
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
			'contact' => [
				'facebook' => 'klapuchdominik',
				'instagram' => null,
				'phone_number' => null,
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
			'contact' => [
				'facebook' => 'klapuchdominik2',
				'instagram' => null,
				'phone_number' => null,
			],
		]);
		$rows = (new Storage\NativeQuery($this->connection, 'SELECT * FROM seekers'))->rows();
		$evolutions = (new Storage\NativeQuery($this->connection, 'SELECT * FROM evolutions'))->rows();
		Assert::count(2, $rows);
		Assert::count(2, $evolutions);
		Assert::same(1, $rows[0]['id']);
		Assert::same(2, $rows[1]['id']);
	}

	public function testThrowingOnDuplicatedEmail(): void {
		$register = function() {
			(new Access\UniqueSeekers(
				$this->connection,
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
				'contact' => [
					'facebook' => 'klapuchdominik',
					'instagram' => null,
					'phone_number' => null,
				],
			]);
		};
		$register();
		Assert::exception($register, \UnexpectedValueException::class, 'Email foo@bar.cz already exists');
	}

	public function testThrowingOnDuplicatedContact(): void {
		$register = function(string $email) {
			return function () use ($email) {
				(new Access\UniqueSeekers(
					$this->connection,
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
					'contact' => [
						'facebook' => 'klapuchdominik',
						'instagram' => null,
						'phone_number' => null,
					],
				]);
			};
		};
		$register('foo@bar.cz')();
		Assert::exception($register('foo@baz.cz'), \UnexpectedValueException::class, 'Facebook klapuchdominik already exists');
	}
}

(new UniqueSeekersTest())->run();
