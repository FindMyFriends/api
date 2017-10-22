<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ApiErrorCallback extends \Tester\TestCase {
	public function testTransformingStatusCodeOnThrowing() {
		$ex = Assert::exception(function() {
			(new Misc\ApiErrorCallback(403))->invoke(function() {
				throw new \DomainException('ABC', 100);
			});
		}, new \DomainException, 'ABC', 403);
		Assert::type(\DomainException::class, $ex->getPrevious());
	}

	public function testNoExceptionWithoutThrowing() {
		Assert::noError(function() {
			(new Misc\ApiErrorCallback(
				403
			))->invoke('strlen', ['abc']);
		});
	}

	public function testReturningValue() {
		Assert::same(
			3,
			(new Misc\ApiErrorCallback(
				403
			))->invoke('strlen', ['abc'])
		);
	}
}

(new ApiErrorCallback())->run();