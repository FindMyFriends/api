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

final class EvolutionRule extends Tester\TestCase {
	public function testApplicationWithAllReturnedValues() {
		Assert::same(
			['evolved_at' => '2017-09-17T13:58:10+00:00'],
			(new Constraint\EvolutionRule())->apply(['evolved_at' => '2017-09-17T13:58:10+00:00'])
		);
	}

	public function testConvertingAgeToBirthYear() {
		Assert::same(
			[
				'evolved_at' => '2015-09-17T13:58:10+00:00',
				'general' => [
					'birth_year' => ['from' => 1990, 'to' => 1996],
				],
			],
			(new Constraint\EvolutionRule())->apply(
				[
					'evolved_at' => '2015-09-17T13:58:10+00:00',
					'general' => [
						'age' => ['from' => 19, 'to' => 25],
					],
				]
			)
		);
	}
}

(new EvolutionRule())->run();