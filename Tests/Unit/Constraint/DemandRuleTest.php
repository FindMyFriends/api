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

final class DemandRuleTest extends Tester\TestCase {
	private const BASE = [
		'body' => [
			'breast_size' => null,
			'height' => ['value' => 10, 'unit' => 'mm'],
			'weight' => ['value' => 100, 'unit' => 'kg'],
		],
		'hair' => [
			'length' => ['value' => 10, 'unit' => 'mm'],
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
			'length' => [
				'value' => null,
				'unit' => null,
			],
		],
		'hands' => [
			'nails' => [
				'length' => [
					'value' => null,
					'unit' => null,
				],
			],
		],
	];

	public function testApplicationWithAllReturnedValues() {
		Assert::equal(self::BASE, (new Constraint\DemandRule())->apply(self::BASE));
	}
}

(new DemandRuleTest())->run();
