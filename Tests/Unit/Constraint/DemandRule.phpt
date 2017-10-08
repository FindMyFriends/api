<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class DemandRule extends \Tester\TestCase {
	public function testApplicationWithAllReturnedValues() {
		Assert::same(
			['general' => ['age' => '[20,22]']],
			(new Constraint\DemandRule())->apply(['general' => ['age' => '[20,22]']])
		);
	}

	public function testMatchingSatisfaction() {
		Assert::true(
			(new Constraint\DemandRule())->satisfied(
				['general' => ['age' => '[20,22]']]
			)
		);
	}
}

(new DemandRule())->run();