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
final class AgeRangeRuleTest extends TestCase\Runtime {
	public function testPassingOnFromAndToAsProperRange(): void {
		$age = ['from' => 20, 'to' => 30];
		Assert::true((new Constraint\AgeRangeRule())->satisfied($age));
		Assert::noError(static function() use ($age) {
			(new Constraint\AgeRangeRule())->apply($age);
		});
	}

	public function testPassingOnFromAndToAsSame(): void {
		$age = ['from' => 30, 'to' => 30];
		Assert::true((new Constraint\AgeRangeRule())->satisfied($age));
		Assert::noError(static function() use ($age) {
			(new Constraint\AgeRangeRule())->apply($age);
		});
	}

	public function testThrowingForSwapped(): void {
		$age = ['from' => 30, 'to' => 10];
		Assert::false((new Constraint\AgeRangeRule())->satisfied($age));
		Assert::exception(static function() use ($age) {
			(new Constraint\AgeRangeRule())->apply($age);
		}, \UnexpectedValueException::class, 'Age must be properly ordered as range');
	}
}

(new AgeRangeRuleTest())->run();
