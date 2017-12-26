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
				'general' => ['gender' => 'man'],
				'face' => ['beard' => ['care' => 10]],
				'location' => [
					'met_at' => [
						'from' => '2015-09-17T13:58:10+00:00',
						'to' => '2017-10-17T13:58:10+00:00',
					],
				],
			],
			(new Constraint\DemandRule())->apply(
				[
					'general' => ['gender' => 'man'],
					'face' => ['beard' => ['care' => 10]],
					'location' => [
						'met_at' => [
							'from' => '2015-09-17T13:58:10+00:00',
							'to' => '2017-10-17T13:58:10+00:00',
						],
					],
				]
			)
		);
	}
}

(new DemandRuleTest())->run();