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
	public function testApplicationWithAllReturnedValues() {
		Assert::equal(
			[
				'body' => ['breast_size' => null],
				'general' => ['gender' => 'man'],
				'beard' => ['care' => 10],
				'location' => [
					'met_at' => [
						'moment' => '2015-09-17T13:58:10+00:00',
						'approximation' => 'PT2H',
					],
				],
			],
			(new Constraint\DemandRule())->apply(
				[
					'body' => ['breast_size' => null],
					'general' => ['gender' => 'man'],
					'beard' => ['care' => 10],
					'location' => [
						'met_at' => [
							'moment' => '2015-09-17T13:58:10+00:00',
							'approximation' => 'PT2H',
						],
					],
				]
			)
		);
	}
}

(new DemandRuleTest())->run();