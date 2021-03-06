<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ApiErrorCallbackTest extends TestCase\Runtime {
	public function testTransformingStatusCodeOnThrowing(): void {
		$ex = Assert::exception(static function() {
			(new Misc\ApiErrorCallback(HTTP_FORBIDDEN))->invoke(static function() {
				throw new \UnexpectedValueException('ABC', 100);
			});
		}, \UnexpectedValueException::class, 'ABC', HTTP_FORBIDDEN);
		Assert::type(\UnexpectedValueException::class, $ex->getPrevious());
	}

	public function testRethrowingOnOtherExceptions(): void {
		$ex = Assert::exception(static function() {
			(new Misc\ApiErrorCallback(HTTP_FORBIDDEN))->invoke(static function() {
				throw new \DomainException('ABC', 100);
			});
		}, \DomainException::class, 'ABC', 100);
		Assert::null($ex->getPrevious());
	}

	public function testNoExceptionWithoutThrowing(): void {
		Assert::noError(static function() {
			(new Misc\ApiErrorCallback(
				HTTP_FORBIDDEN
			))->invoke('strlen', ['abc']);
		});
	}

	public function testReturningValue(): void {
		Assert::same(
			3,
			(new Misc\ApiErrorCallback(
				HTTP_FORBIDDEN
			))->invoke('strlen', ['abc'])
		);
	}
}

(new ApiErrorCallbackTest())->run();
