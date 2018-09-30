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
final class DateTimeRuleTest extends TestCase\Runtime {
	public function testPassingWithIso8601(): void {
		Assert::true((new Constraint\DateTimeRule())->satisfied('2017-09-17T13:58:10+00:00'));
		Assert::noError(static function() {
			(new Constraint\DateTimeRule())->apply('2017-09-17T13:58:10+00:00');
		});
	}

	public function testPassingWithJavascriptPreferredIso8601(): void {
		Assert::true((new Constraint\DateTimeRule())->satisfied('2018-05-27T19:14:02.232+02:00'));
		Assert::noError(static function() {
			(new Constraint\DateTimeRule())->apply('2018-05-27T19:14:02.232+02:00');
		});
	}

	public function testFailingOnCustomFormat(): void {
		Assert::false((new Constraint\DateTimeRule())->satisfied('2017-09-17 13:58:10'));
		Assert::exception(static function() {
			(new Constraint\DateTimeRule())->apply('2017-09-17 13:58:10');
		}, \UnexpectedValueException::class, 'Datetime must be in ISO8601');
	}

	public function testNoModification(): void {
		Assert::same(
			'2017-09-17T13:58:10+00:00',
			(new Constraint\DateTimeRule())->apply('2017-09-17T13:58:10+00:00')
		);
	}
}

(new DateTimeRuleTest())->run();
