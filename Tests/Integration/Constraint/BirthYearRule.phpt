<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class BirthYearRule extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testPassingOnAgeInRange() {
		Assert::true((new Constraint\BirthYearRule($this->database))->satisfied(['from' => 1996, 'to' => 1997]));
		Assert::noError(function() {
			(new Constraint\BirthYearRule($this->database))->apply(['from' => 1996, 'to' => 1997]);
		});
	}

	public function testPassingOnNewBorn() {
		Assert::true((new Constraint\BirthYearRule($this->database))->satisfied(['from' => 1996, 'to' => date('Y')]));
	}

	public function testFailingOnTooOld() {
		Assert::false((new Constraint\BirthYearRule($this->database))->satisfied(['from' => 1700, 'to' => 1996]));
		Assert::exception(
			function() {
				(new Constraint\BirthYearRule($this->database))->apply(['from' => 1700, 'to' => 1996]);
			},
			\UnexpectedValueException::class,
			sprintf('Birth year must be in range from 1850 to %d', date('Y'))
		);
	}

	public function testFailingOnTooYoung() {
		Assert::false((new Constraint\BirthYearRule($this->database))->satisfied(['from' => 1996, 'to' => date('Y') + 1]));
		Assert::false((new Constraint\BirthYearRule($this->database))->satisfied(['from' => 1996, 'to' => 2030]));
	}
}

(new BirthYearRule())->run();