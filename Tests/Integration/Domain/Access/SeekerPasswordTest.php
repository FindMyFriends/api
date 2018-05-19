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
use Klapuch\Encryption;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class SeekerPasswordTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testChangingWithHashing() {
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'seeker', ['password' => 'pass']))->try();
		(new Access\SeekerPassword(
			new Access\FakeSeeker((string) $id),
			$this->database,
			new Encryption\FakeCipher()
		))->change('willBeEncrypted');
		$seeker = (new Storage\NativeQuery(
			$this->database,
			'SELECT * FROM seekers WHERE id = ?',
			[$id]
		))->row();
		Assert::same('secret', $seeker['password']);
	}

	public function testChangingWithoutAffectingOthers() {
		(new Misc\SamplePostgresData($this->database, 'seeker', ['password' => 'pass']))->try();
		(new Misc\SamplePostgresData($this->database, 'seeker', ['password' => 'pass']))->try();
		(new Access\SeekerPassword(
			new Access\FakeSeeker('1'),
			$this->database,
			new Encryption\FakeCipher()
		))->change('willBeEncrypted');
		$seekers = (new Storage\NativeQuery(
			$this->database,
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
