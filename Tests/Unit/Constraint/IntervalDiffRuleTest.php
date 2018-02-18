<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class IntervalDiffRuleTest extends Tester\TestCase {
	public function testAllowingInRange() {
		Assert::true((new Constraint\IntervalDiffRule('PT20H'))->satisfied('PT10H'));
		Assert::noError(function() {
			(new Constraint\IntervalDiffRule('PT20H'))->apply('PT10H');
		});
	}

	public function testAllowingSameAsMax() {
		Assert::true((new Constraint\IntervalDiffRule('PT20H'))->satisfied('PT20H'));
	}

	public function testThrowingOutOfMax() {
		Assert::false((new Constraint\IntervalDiffRule('PT20H'))->satisfied('P1D'));
		Assert::exception(function() {
			(new Constraint\IntervalDiffRule('PT20H'))->apply('P1D');
		}, \UnexpectedValueException::class, 'Max diff is "PT20H", given "P1D"');
	}
}

(new IntervalDiffRuleTest())->run();