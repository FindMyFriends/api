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

final class SecureVerificationCodes extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testGenerating() {
		(new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'me@me.cz']))->try();
		$verification = (new Access\SecureVerificationCodes($this->database))->generate('me@me.cz');
		$code = (new Storage\NativeQuery(
			$this->database,
			'SELECT code FROM verification_codes'
		))->field();
		Assert::equal(
			new Access\ThrowawayVerificationCode($code, $this->database),
			$verification
		);
	}
}

(new SecureVerificationCodes())->run();
