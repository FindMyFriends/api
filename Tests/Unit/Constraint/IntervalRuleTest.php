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

final class IntervalRuleTest extends Tester\TestCase {
	public function testPassingWithIso8601() {
		Assert::true((new Constraint\IntervalRule(''))->satisfied('PT10H'));
		Assert::noError(function() {
			(new Constraint\IntervalRule(''))->apply('PT10H');
		});
	}

	public function testFailingOnCustomFormat() {
		Assert::false((new Constraint\IntervalRule(''))->satisfied('PT10Habc'));
		Assert::exception(function() {
			(new Constraint\IntervalRule('root.interval'))->apply('PT10Habc');
		}, \UnexpectedValueException::class, 'root.interval - interval must be in ISO8601');
	}

	public function testNoModification() {
		Assert::same('PT10H', (new Constraint\IntervalRule(''))->apply('PT10H'));
	}
}

(new IntervalRuleTest())->run();