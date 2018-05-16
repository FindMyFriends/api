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
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class VerifiedEntrance extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	/**
	 * @throws \UnexpectedValueException Email has not been verified yet
	 */
	public function testThrowingOnNotVerifiedEmail() {
		(new Access\VerifiedEntrance(
			$this->database,
			new Access\FakeEntrance(new Access\FakeSeeker())
		))->enter(['unverified@bar.cz', 'heslo']);
	}

	public function testPassingOnCaseInsensitiveVerifiedEmail() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'verified@bar.cz']))->try();
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['used_at' => '2005-01-01', 'seeker_id' => $seeker]))->try();
		$seeker = new Access\FakeSeeker((string) $seeker);
		Assert::same(
			$seeker,
			(new Access\VerifiedEntrance(
				$this->database,
				new Access\FakeEntrance($seeker)
			))->enter(['VERIFIED@bar.cz', 'heslo'])
		);
	}

	public function testPassingWithStringObject() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'verified@bar.cz']))->try();
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['used_at' => '2005-01-01', 'seeker_id' => $seeker]))->try();
		Assert::noError(function() use ($seeker) {
			(new Access\VerifiedEntrance(
				$this->database,
				new Access\FakeEntrance(new Access\FakeSeeker((string) $seeker))
			))->enter(
				[
					new class {
						public function __toString() {
							return 'VERIFIED@bar.cz';
						}
					},
					'heslo',
				]
			);
		});
	}
}

(new VerifiedEntrance())->run();
