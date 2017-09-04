<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ETag extends \Tester\TestCase {
	public function testHexFormat() {
		Assert::match('"%h%"', (string) new Misc\ETag(new \stdClass()));
	}

	public function testSameClassesWithSameTag() {
		Assert::same(
			(string) new Misc\ETag(new \stdClass()),
			(string) new Misc\ETag(new \stdClass())
		);
	}

	public function testDifferentClassesWithDifferentTag() {
		Assert::notSame(
			(string) new Misc\ETag(new \ArrayIterator()),
			(string) new Misc\ETag(new \stdClass())
		);
	}

	public function testAllowedForAnonymousClass() {
		Assert::noError(function() {
			(string) new Misc\ETag(new class {
			});
		});
	}
}

(new ETag())->run();