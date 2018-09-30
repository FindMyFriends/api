<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ExistingVerificationCodeTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testThrowingOnUnknownCode() {
		Assert::exception(function() {
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode(),
				'unknown:code',
				$this->connection
			))->use();
		}, \UnexpectedValueException::class, 'The verification code does not exist');
		Assert::exception(function() {
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode(),
				'unknown:code',
				$this->connection
			))->print(new Output\FakeFormat(''));
		}, \UnexpectedValueException::class, 'The verification code does not exist');
	}

	public function testPassingOnUsingKnownCode() {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->connection))->try();
		Assert::noError(
			function() use ($code) {
				(new Access\ExistingVerificationCode(
					new Access\FakeVerificationCode(),
					$code,
					$this->connection
				))->use();
			}
		);
	}

	public function testPrintingCodeWithOrigin() {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->connection))->try();
		Assert::same(
			sprintf('|abc|def||code|%s|', $code),
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode(new Output\FakeFormat('|abc|def|')),
				$code,
				$this->connection
			))->print(new Output\FakeFormat(''))->serialization()
		);
	}

	public function testThrowingOnUsingCaseInsensitiveCode() {
		['verification_code' => ['code' => $code]] = (new Misc\SampleSeeker($this->connection))->try();
		Assert::exception(function() use ($code) {
			(new Access\ExistingVerificationCode(
				new Access\FakeVerificationCode(),
				strtoupper($code),
				$this->connection
			))->use();
		}, \UnexpectedValueException::class, 'The verification code does not exist');
	}
}

(new ExistingVerificationCodeTest())->run();
