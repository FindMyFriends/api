<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ApiErrorCallbackTest extends Tester\TestCase {
	public function testTransformingStatusCodeOnThrowing() {
		$ex = Assert::exception(function() {
			(new Misc\ApiErrorCallback(HTTP_FORBIDDEN))->invoke(function() {
				throw new \DomainException('ABC', 100);
			});
		}, new \DomainException, 'ABC', HTTP_FORBIDDEN);
		Assert::type(\DomainException::class, $ex->getPrevious());
	}

	public function testNoExceptionWithoutThrowing() {
		Assert::noError(function() {
			(new Misc\ApiErrorCallback(
				HTTP_FORBIDDEN
			))->invoke('strlen', ['abc']);
		});
	}

	public function testReturningValue() {
		Assert::same(
			3,
			(new Misc\ApiErrorCallback(
				HTTP_FORBIDDEN
			))->invoke('strlen', ['abc'])
		);
	}
}

(new ApiErrorCallbackTest())->run();