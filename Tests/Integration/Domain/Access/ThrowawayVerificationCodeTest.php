<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ThrowawayVerificationCodeTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testMakeCodeUsedAfterUsage(): void {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->connection))->try();
		(new Access\ThrowawayVerificationCode($code, $this->connection))->use();
		Assert::true(
			(new Storage\NativeQuery(
				$this->connection,
				'SELECT used_at IS NOT NULL
				FROM verification_codes
				WHERE code = ?',
				[$code]
			))->field()
		);
	}

	public function testThrowingOnUsingAlreadyActivatedCode(): void {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->connection, ['email' => 'foo@bar.cz', 'verification_code' => ['used_at' => 'NOW()']]))->try();
		Assert::exception(function() use ($code) {
			(new Access\ThrowawayVerificationCode(
				$code,
				$this->connection
			))->use();
		}, \UnexpectedValueException::class, 'Verification code was already used');
		Assert::exception(function() use ($code) {
			(new Access\ThrowawayVerificationCode(
				$code,
				$this->connection
			))->print(new Output\FakeFormat(''));
		}, \UnexpectedValueException::class, 'Verification code was already used');
	}

	public function testPrintingCode(): void {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->connection))->try();
		Assert::same(
			sprintf('|code|%s|', $code),
			(new Access\ThrowawayVerificationCode(
				$code,
				$this->connection
			))->print(new Output\FakeFormat(''))->serialization()
		);
	}
}

(new ThrowawayVerificationCodeTest())->run();
