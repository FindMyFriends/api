<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ReserveVerificationCodesTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testRegenerating() {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->connection, ['email' => 'foo@bar.cz']))->try();
		Assert::equal(
			new Access\ThrowawayVerificationCode($code, $this->connection),
			(new Access\ReserveVerificationCodes(
				$this->connection
			))->generate('foo@bar.cz')
		);
	}

	/**
	 * @throws \UnexpectedValueException For the given email, there is no valid verification code
	 */
	public function testThrowingOnRegeneratingForOnceUsedCode() {
		(new Misc\SampleSeeker($this->connection, ['email' => 'foo@bar.cz', 'verification_code' => ['used_at' => 'NOW()']]))->try();
		(new Access\ReserveVerificationCodes(
			$this->connection
		))->generate('foo@bar.cz');
	}
}

(new ReserveVerificationCodesTest())->run();
