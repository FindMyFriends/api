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

final class OpenClosedRange extends Tester\TestCase {
	public function testPassingOnFullyOpenInterval() {
		Assert::true((new Constraint\OpenClosedRange())->satisfied('(10,20)'));
	}

	public function testPassingOnFullyClosedInterval() {
		Assert::true((new Constraint\OpenClosedRange())->satisfied('[10,20]'));
	}

	public function testPassingOnPartlyClosedInterval() {
		Assert::true((new Constraint\OpenClosedRange())->satisfied('(10,20]'));
		Assert::true((new Constraint\OpenClosedRange())->satisfied('[10,20)'));
	}

	public function testFailingOnNotSupportedOperator() {
		Assert::false((new Constraint\OpenClosedRange())->satisfied('{10,20)'));
		Assert::false((new Constraint\OpenClosedRange())->satisfied('(10,20}'));
		Assert::false((new Constraint\OpenClosedRange())->satisfied('(10,20}'));
	}

	public function testAcceptingAnyIntervalType() {
		Assert::true((new Constraint\OpenClosedRange())->satisfied('(abc,20)'));
		Assert::true((new Constraint\OpenClosedRange())->satisfied('(20,abc)'));
		Assert::true((new Constraint\OpenClosedRange())->satisfied('("2017-01-01","2017-01-02")'));
	}

	public function testFailingOnEmpty() {
		Assert::false((new Constraint\OpenClosedRange())->satisfied(''));
		Assert::false((new Constraint\OpenClosedRange())->satisfied('()'));
		Assert::false((new Constraint\OpenClosedRange())->satisfied('[]'));
		Assert::false((new Constraint\OpenClosedRange())->satisfied('{}'));
	}

	public function testPassingOnEmptyBeginAndEnd() {
		Assert::true((new Constraint\OpenClosedRange())->satisfied('(,)'));
	}

	public function testThrowingOnBadApplication() {
		Assert::exception(
			function() {
				(new Constraint\OpenClosedRange())->apply('(10,20}');
			},
			\UnexpectedValueException::class,
			'Only open/closed ranges are allowed'
		);
	}

	public function testApplicationWithoutModification() {
		Assert::same('(10,20)', (new Constraint\OpenClosedRange())->apply('(10,20)'));
	}
}

(new OpenClosedRange())->run();