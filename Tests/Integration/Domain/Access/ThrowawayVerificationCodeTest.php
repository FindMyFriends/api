<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ThrowawayVerificationCodeTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testMakeCodeUsedAfterUsage() {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->database))->try();
		(new Access\ThrowawayVerificationCode($code, $this->database))->use();
		Assert::true(
			(new Storage\NativeQuery(
				$this->database,
				'SELECT used_at IS NOT NULL
				FROM verification_codes
				WHERE code = ?',
				[$code]
			))->field()
		);
	}

	public function testThrowingOnUsingAlreadyActivatedCode() {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->database, ['email' => 'foo@bar.cz', 'verification_code' => ['used_at' => 'NOW()']]))->try();
		Assert::exception(function() use ($code) {
			(new Access\ThrowawayVerificationCode(
				$code,
				$this->database
			))->use();
		}, \UnexpectedValueException::class, 'Verification code was already used');
		Assert::exception(function() use ($code) {
			(new Access\ThrowawayVerificationCode(
				$code,
				$this->database
			))->print(new Output\FakeFormat(''));
		}, \UnexpectedValueException::class, 'Verification code was already used');
	}

	public function testPrintingCode() {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->database))->try();
		Assert::same(
			sprintf('|code|%s|', $code),
			(new Access\ThrowawayVerificationCode(
				$code,
				$this->database
			))->print(new Output\FakeFormat(''))->serialization()
		);
	}
}

(new ThrowawayVerificationCodeTest())->run();
