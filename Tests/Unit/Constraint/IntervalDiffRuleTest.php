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
final class IntervalDiffRuleTest extends TestCase\Runtime {
	public function testAllowingInRange(): void {
		Assert::true((new Constraint\IntervalDiffRule('PT20H'))->satisfied('PT10H'));
		Assert::noError(static function() {
			(new Constraint\IntervalDiffRule('PT20H'))->apply('PT10H');
		});
	}

	public function testAllowingSameAsMax(): void {
		Assert::true((new Constraint\IntervalDiffRule('PT20H'))->satisfied('PT20H'));
	}

	public function testThrowingOutOfMax(): void {
		Assert::false((new Constraint\IntervalDiffRule('PT20H'))->satisfied('P1D'));
		Assert::exception(static function() {
			(new Constraint\IntervalDiffRule('PT20H'))->apply('P1D');
		}, \UnexpectedValueException::class, 'Max diff is "PT20H", given "P1D"');
	}
}

(new IntervalDiffRuleTest())->run();
