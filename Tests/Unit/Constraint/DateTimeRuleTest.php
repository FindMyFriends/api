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

final class DateTimeRuleTest extends Tester\TestCase {
	public function testPassingWithIso8601() {
		Assert::true((new Constraint\DateTimeRule(''))->satisfied('2017-09-17T13:58:10+00:00'));
		Assert::noError(function() {
			(new Constraint\DateTimeRule(''))->apply('2017-09-17T13:58:10+00:00');
		});
	}

	public function testFailingOnCustomFormat() {
		Assert::false((new Constraint\DateTimeRule(''))->satisfied('2017-09-17 13:58:10'));
		Assert::exception(function() {
			(new Constraint\DateTimeRule('root.inserted_at'))->apply('2017-09-17 13:58:10');
		}, \UnexpectedValueException::class, 'root.inserted_at - datetime must be in ISO8601');
	}

	public function testNoModification() {
		Assert::same(
			'2017-09-17T13:58:10+00:00',
			(new Constraint\DateTimeRule(''))->apply('2017-09-17T13:58:10+00:00')
		);
	}
}

(new DateTimeRuleTest())->run();