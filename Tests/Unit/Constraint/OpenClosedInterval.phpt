<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class OpenClosedInterval extends \Tester\TestCase {
	public function testPassingOnFullyOpenInterval() {
		Assert::true((new Constraint\OpenClosedInterval())->satisfied('(10,20)'));
		$this->testApplicationWithoutModification();
	}

	public function testPassingOnFullyClosedInterval() {
		Assert::true((new Constraint\OpenClosedInterval())->satisfied('[10,20]'));
	}

	public function testPassingOnPartlyClosedInterval() {
		Assert::true((new Constraint\OpenClosedInterval())->satisfied('(10,20]'));
		Assert::true((new Constraint\OpenClosedInterval())->satisfied('[10,20)'));
	}

	public function testFailingOnNotSupportedOperator() {
		Assert::false((new Constraint\OpenClosedInterval())->satisfied('{10,20)'));
		Assert::false((new Constraint\OpenClosedInterval())->satisfied('(10,20}'));
		$this->testThrowingOnBadApplication();
		Assert::false((new Constraint\OpenClosedInterval())->satisfied('(10,20}'));
	}

	public function testFailingOnNotNumericBase() {
		Assert::false((new Constraint\OpenClosedInterval())->satisfied('(abc,20)'));
		Assert::false((new Constraint\OpenClosedInterval())->satisfied('(20,abc)'));
	}

	public function testFailingOnEmpty() {
		Assert::false((new Constraint\OpenClosedInterval())->satisfied(''));
		Assert::false((new Constraint\OpenClosedInterval())->satisfied('()'));
		Assert::false((new Constraint\OpenClosedInterval())->satisfied('[]'));
		Assert::false((new Constraint\OpenClosedInterval())->satisfied('{}'));
	}

	public function testThrowingOnBadApplication() {
		Assert::exception(
			function() {
				(new Constraint\OpenClosedInterval())->apply('(10,20}');
			},
			\UnexpectedValueException::class,
			'Allowed only open/closed numeric intervals'
		);
	}

	public function testApplicationWithoutModification() {
		Assert::same('(10,20)', (new Constraint\OpenClosedInterval())->apply('(10,20)'));
	}
}

(new OpenClosedInterval())->run();