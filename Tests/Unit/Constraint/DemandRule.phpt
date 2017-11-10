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

final class DemandRule extends Tester\TestCase {
	public function testApplicationWithAllReturnedValues() {
		Assert::same(
			[
				'location' => [
					'met_at' => [
						'from' => '2017-09-17T13:58:10+00:00',
						'to' => '2017-10-17T13:58:10+00:00',
					],
				],
			],
			(new Constraint\DemandRule())->apply(
				[
					'location' => [
						'met_at' => [
							'from' => '2017-09-17T13:58:10+00:00',
							'to' => '2017-10-17T13:58:10+00:00',
						],
					],
				]
			)
		);
	}
}

(new DemandRule())->run();