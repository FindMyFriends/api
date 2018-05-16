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
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ReserveVerificationCodes extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testRegenerating() {
		$code = str_repeat('a', 91);
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['code' => $code, 'used_at' => null, 'seeker_id' => $seeker]))->try();
		Assert::equal(
			new Access\ThrowawayVerificationCode($code, $this->database),
			(new Access\ReserveVerificationCodes(
				$this->database
			))->generate('foo@bar.cz')
		);
	}

	/**
	 * @throws \UnexpectedValueException For the given email, there is no valid verification code
	 */
	public function testThrowingOnRegeneratingForOnceUsedCode() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['used_at' => '2005-01-01', 'seeker_id' => $seeker]))->try();
		(new Access\ReserveVerificationCodes(
			$this->database
		))->generate('foo@bar.cz');
	}
}

(new ReserveVerificationCodes())->run();
