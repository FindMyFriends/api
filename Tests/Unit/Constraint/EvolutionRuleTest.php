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

final class EvolutionRuleTest extends Tester\TestCase {
	public function testApplicationWithAllReturnedValues() {
		Assert::equal(
			[
				'body' => ['breast_size' => null],
				'general' => ['gender' => 'man'],
				'beard' => ['care' => 10],
				'evolved_at' => '2017-09-17T13:58:10+00:00',
			],
			(new Constraint\EvolutionRule())->apply(
				[
					'body' => ['breast_size' => null],
					'general' => ['gender' => 'man'],
					'beard' => ['care' => 10],
					'evolved_at' => '2017-09-17T13:58:10+00:00',
				]
			)
		);
	}
}

(new EvolutionRuleTest())->run();