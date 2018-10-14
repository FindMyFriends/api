<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class SoulmateRuleTest extends TestCase\Runtime {
	public function testApplicationWithAllReturnedValues(): void {
		$base = ['is_exposed' => true, 'foo' => 'bar'];
		Assert::same($base, (new Constraint\SoulmateRule())->apply($base));
	}

	public function testThrowingOnRevertingExpose(): void {
		Assert::exception(static function() {
			$base = ['is_exposed' => false];
			(new Constraint\SoulmateRule())->apply($base);
		}, \UnexpectedValueException::class, 'Property is_exposed is only possible to change to true');
	}

	public function testCheckingSetValues(): void {
		$base = ['foo' => 'bar'];
		Assert::same($base, (new Constraint\SoulmateRule())->apply($base));
	}
}

(new SoulmateRuleTest())->run();
