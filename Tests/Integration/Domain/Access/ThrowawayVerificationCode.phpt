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
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ThrowawayVerificationCode extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testMakeCodeUsedAfterUsage() {
		$code = str_repeat('x', 91);
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['code' => $code, 'used_at' => null]))->try();
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
		$code = str_repeat('x', 91);
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['code' => $code, 'used_at' => '2005-01-01']))->try();
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
		$code = str_repeat('x', 91);
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['code' => $code, 'used_at' => null]))->try();
		Assert::same(
			sprintf('|code|%s|', $code),
			(new Access\ThrowawayVerificationCode(
				$code,
				$this->database
			))->print(new Output\FakeFormat(''))->serialization()
		);
	}
}

(new ThrowawayVerificationCode())->run();
