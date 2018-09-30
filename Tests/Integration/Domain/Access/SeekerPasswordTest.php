<?php
declare(strict_types = 1);

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
final class SeekerPasswordTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testChangingWithHashing(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'pass']))->try();
		(new Access\SeekerPassword(
			new Access\FakeSeeker((string) $id),
			$this->connection,
			new Encryption\FakeCipher()
		))->change('willBeEncrypted');
		$seeker = (new Storage\NativeQuery(
			$this->connection,
			'SELECT * FROM seekers WHERE id = ?',
			[$id]
		))->row();
		Assert::same('secret', $seeker['password']);
	}

	public function testChangingWithoutAffectingOthers(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'pass']))->try();
		(new Misc\SamplePostgresData($this->connection, 'seeker', ['password' => 'pass']))->try();
		(new Access\SeekerPassword(
			new Access\FakeSeeker('1'),
			$this->connection,
			new Encryption\FakeCipher()
		))->change('willBeEncrypted');
		$seekers = (new Storage\NativeQuery(
			$this->connection,
			'SELECT * FROM seekers'
		))->rows();
		Assert::count(2, $seekers);
		Assert::same(2, $seekers[0]['id']);
		Assert::same(1, $seekers[1]['id']);
		Assert::same('pass', $seekers[0]['password']);
		Assert::same('secret', $seekers[1]['password']);
	}
}

(new SeekerPasswordTest())->run();
