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

final class AgeRangeRuleTest extends Tester\TestCase {
	public function testPassingOnFromAndToAsProperRange() {
		$age = ['from' => 20, 'to' => 30];
		Assert::true((new Constraint\AgeRangeRule())->satisfied($age));
		Assert::noError(static function() use ($age) {
			(new Constraint\AgeRangeRule())->apply($age);
		});
	}

	public function testPassingOnFromAndToAsSame() {
		$age = ['from' => 30, 'to' => 30];
		Assert::true((new Constraint\AgeRangeRule())->satisfied($age));
		Assert::noError(static function() use ($age) {
			(new Constraint\AgeRangeRule())->apply($age);
		});
	}

	public function testThrowingForSwapped() {
		$age = ['from' => 30, 'to' => 10];
		Assert::false((new Constraint\AgeRangeRule())->satisfied($age));
		Assert::exception(static function() use ($age) {
			(new Constraint\AgeRangeRule())->apply($age);
		}, \UnexpectedValueException::class, 'Age must be properly ordered as range');
	}
}

(new AgeRangeRuleTest())->run();
