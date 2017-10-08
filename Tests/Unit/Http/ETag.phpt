<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Http;

use FindMyFriends\Http;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ETag extends \Tester\TestCase {
	public function testHexFormat() {
		Assert::match('"%h%"', (string) new Http\ETag(new \stdClass()));
	}

	public function testSameClassesWithSameTag() {
		Assert::same(
			(string) new Http\ETag(new \stdClass()),
			(string) new Http\ETag(new \stdClass())
		);
	}

	public function testDifferentClassesWithDifferentTag() {
		Assert::notSame(
			(string) new Http\ETag(new \ArrayIterator()),
			(string) new Http\ETag(new \stdClass())
		);
	}

	public function testAllowedForAnonymousClass() {
		Assert::noError(function() {
			(string) new Http\ETag(new class {
			});
		});
	}
}

(new ETag())->run();