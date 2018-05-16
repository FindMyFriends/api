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
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class ExistingVerificationCode extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknownCode() {
		Assert::exception(function() {
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode(),
				'unknown:code',
				$this->database
			))->use();
		}, \UnexpectedValueException::class, 'The verification code does not exist');
		Assert::exception(function() {
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode(),
				'unknown:code',
				$this->database
			))->print(new Output\FakeFormat(''));
		}, \UnexpectedValueException::class, 'The verification code does not exist');
	}

	public function testPassingOnUsingKnownCode() {
		$code = str_repeat('x', 91);
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['code' => $code]))->try();
		Assert::noError(
			function() use ($code) {
				(new Access\ExistingVerificationCode(
					new Access\FakeVerificationCode(),
					$code,
					$this->database
				))->use();
			}
		);
	}

	public function testPrintingCodeWithOrigin() {
		$code = str_repeat('x', 91);
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['code' => $code]))->try();
		Assert::same(
			sprintf('|abc|def||code|%s|', $code),
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode(new Output\FakeFormat('|abc|def|')),
				$code,
				$this->database
			))->print(new Output\FakeFormat(''))->serialization()
		);
	}

	public function testThrowingOnUsingCaseInsensitiveCode() {
		$code = str_repeat('x', 91);
		(new Misc\SamplePostgresData($this->database, 'verification_code', ['code' => $code]))->try();
		Assert::exception(function() use ($code) {
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode(),
				strtoupper($code),
				$this->database
			))->use();
		}, \UnexpectedValueException::class, 'The verification code does not exist');
	}
}

(new ExistingVerificationCode())->run();
