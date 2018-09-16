<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class EvolutionRuleTest extends Tester\TestCase {
	public function testApplicationWithAllReturnedValues() {
		Assert::equal(
			[
				'body' => [
					'breast_size' => null,
				],
				'hair' => [
					'length_id' => 1,
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
				'general' => ['sex' => 'man'],
				'evolved_at' => '2017-09-17T13:58:10+00:00',
			],
			(new Constraint\EvolutionRule())->apply(
				[
					'body' => [
						'breast_size' => null,
					],
					'hair' => [
						'length_id' => 1,
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
					'general' => ['sex' => 'man'],
					'evolved_at' => '2017-09-17T13:58:10+00:00',
				]
			)
		);
	}
}

(new EvolutionRuleTest())->run();
