<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class DemandRuleTest extends Tester\TestCase {
	private const BASE = [
		'body' => [
			'breast_size' => null,
		],
		'hair' => [
			'length_id' => 1,
		],
		'general' => [
			'sex' => 'man',
			'age' => [
				'from' => 20,
				'to' => 30,
			],
		],
		'beard' => [
			'color_id' => null,
			'care' => 10,
			'length_id' => 2,
		],
		'hands' => [
			'nails' => [
				'length_id' => 3,
			],
		],
	];

	public function testApplicationWithAllReturnedValues() {
		Assert::equal(self::BASE, (new Constraint\DemandRule())->apply(self::BASE));
	}
}

(new DemandRuleTest())->run();
