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

final class LocationRuleTest extends Tester\TestCase {
	private const BASE = [
		'met_at' => [
			'moment' => '2015-09-17T13:58:10+00:00',
			'timeline_side' => 'sooner',
			'approximation' => 'PT2H',
		],
		'coordinates' => [
			'latitude' => 50.5,
			'longitude' => 50.1,
		],
	];

	public function testApplicationWithAllReturnedValues() {
		Assert::equal(self::BASE, (new Constraint\SpotRule())->apply(self::BASE));
	}

	/**
	 * @throws \UnexpectedValueException Exactly timeline side does not have approximation.
	 */
	public function testThrowingOnExactlyTimeLineSideWithApproximation() {
		(new Constraint\SpotRule())->apply(
			array_replace_recursive(
				self::BASE,
				[
					'met_at' => [
						'moment' => '2015-09-17T13:58:10+00:00',
						'timeline_side' => 'exactly',
						'approximation' => 'PT2H',
					],
					'coordinates' => [
						'latitude' => 50.5,
						'longitude' => 50.1,
					],
				]
			)
		);
	}


	public function testAllowedApproximationAsNull()
	{
		Assert::noError(function() {
			(new Constraint\SpotRule())->apply(
				array_replace_recursive(
					self::BASE,
					[
						'met_at' => [
							'moment' => '2015-09-17T13:58:10+00:00',
							'timeline_side' => 'exactly',
							'approximation' => null,
						],
						'coordinates' => [
							'latitude' => 50.5,
							'longitude' => 50.1,
						],
					]
				)
			);
		});
	}
}

(new LocationRuleTest())->run();
