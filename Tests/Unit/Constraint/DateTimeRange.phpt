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

final class DateTimeRange extends Tester\TestCase {
	public function testPassingOnDateTimeRange() {
		Assert::true((new Constraint\DateTimeRange())->satisfied('("2017-01-01","2017-01-02")'));
		Assert::noError(function() {
			(new Constraint\DateTimeRange())->apply('("2017-01-01","2017-01-02")');
		});
	}

	public function testPassingWithAsFull() {
		Assert::true((new Constraint\DateTimeRange())->satisfied('("2017-01-01",)'));
		Assert::true((new Constraint\DateTimeRange())->satisfied('(,"2017-01-02")'));
	}

	public function testFailingOnOtherThanDateTime() {
		Assert::false((new Constraint\DateTimeRange())->satisfied('(10,20)'));
		Assert::false((new Constraint\DateTimeRange())->satisfied('(10,"2017-01-02")'));
		Assert::false((new Constraint\DateTimeRange())->satisfied('("2017-01-02",10)'));
		Assert::exception(function() {
			(new Constraint\DateTimeRange())->apply('(10,20)');
		}, \UnexpectedValueException::class, 'Range must be datetime');
	}

	public function testFailingOnContainingQuoteInQuote() {
		Assert::false((new Constraint\DateTimeRange())->satisfied('("2017"-01-01","2017-01-02")'));
	}

	public function testNoModificationOnApplication() {
			Assert::same('("2017-01-01","2017-01-02")', (new Constraint\DateTimeRange())->apply('("2017-01-01","2017-01-02")'));
	}
}

(new DateTimeRange())->run();