<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class GuestTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testStaticId() {
		Assert::same('0', (new Access\Guest())->id());
	}

	public function testStaticProperties() {
		Assert::same(['role' => 'guest'], (new Access\Guest())->properties());
	}
}

(new GuestTest())->run();
