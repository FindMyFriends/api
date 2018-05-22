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

final class ValueWithUnitRuleTest extends Tester\TestCase {
	public function testPassingOnBothNull() {
		$length = ['value' => null, 'unit' => null];
		Assert::true((new Constraint\ValueWithUnitRule())->satisfied($length));
		Assert::noError(function() use ($length) {
			(new Constraint\ValueWithUnitRule())->apply($length);
		});
	}

	public function testPassingOnBothContainingSomething() {
		$length = ['value' => 10, 'unit' => 'mm'];
		Assert::true((new Constraint\ValueWithUnitRule())->satisfied($length));
		Assert::noError(function() use ($length) {
			(new Constraint\ValueWithUnitRule())->apply($length);
		});
	}

	public function testThrowingOnValueWithoutUnit() {
		$length = ['value' => 10, 'unit' => null];
		Assert::false((new Constraint\ValueWithUnitRule())->satisfied($length));
		Assert::exception(function() use ($length) {
			(new Constraint\ValueWithUnitRule())->apply($length);
		}, \UnexpectedValueException::class, 'Filled value must have unit and vice versa');
	}

	public function testThrowingOnUnitWithoutValue() {
		$length = ['value' => null, 'unit' => 'mm'];
		Assert::false((new Constraint\ValueWithUnitRule())->satisfied($length));
		Assert::exception(function() use ($length) {
			(new Constraint\ValueWithUnitRule())->apply($length);
		}, \UnexpectedValueException::class, 'Filled value must have unit and vice versa');
	}
}

(new ValueWithUnitRuleTest())->run();
