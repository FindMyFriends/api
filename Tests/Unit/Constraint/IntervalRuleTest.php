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
final class IntervalRuleTest extends TestCase\Runtime {
	public function testPassingWithIso8601(): void {
		Assert::true((new Constraint\IntervalRule())->satisfied('PT10H'));
		Assert::noError(static function() {
			(new Constraint\IntervalRule())->apply('PT10H');
		});
	}

	public function testFailingOnCustomFormat(): void {
		Assert::false((new Constraint\IntervalRule())->satisfied('PT10Habc'));
		Assert::exception(static function() {
			(new Constraint\IntervalRule())->apply('PT10Habc');
		}, \UnexpectedValueException::class, 'Interval must be in ISO8601');
	}

	public function testNoModification(): void {
		Assert::same('PT10H', (new Constraint\IntervalRule())->apply('PT10H'));
	}
}

(new IntervalRuleTest())->run();
