<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class SpotRuleTest extends TestCase\Runtime {
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

	public function testApplicationWithAllReturnedValues(): void {
		Assert::equal(self::BASE, (new Constraint\SpotRule())->apply(self::BASE));
	}

	public function testPassingOnSomeMissing(): void {
		Assert::noError(static function () {
			$base = self::BASE;
			unset($base['coordinates']);
			Assert::equal($base, (new Constraint\SpotRule())->apply($base));
		});
		Assert::noError(static function () {
			$base = self::BASE;
			unset($base['met_at']);
			Assert::equal($base, (new Constraint\SpotRule())->apply($base));
		});
	}

	/**
	 * @throws \UnexpectedValueException Exactly timeline side does not have approximation.
	 */
	public function testThrowingOnExactlyTimeLineSideWithApproximation(): void {
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

	public function testAllowedApproximationAsNull(): void
	{
		Assert::noError(static function() {
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

(new SpotRuleTest())->run();
