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
final class DescriptionRuleTest extends Tester\TestCase {
	/**
	 * @throws \UnexpectedValueException Women do not have beards
	 */
	public function testThrowingOnWomanWithBeard() {
		(new Constraint\DescriptionRule())->apply(
			[
				'general' => ['gender' => 'woman'],
				'face' => [
					'beard' => [
						'color_id' => 8,
						'care' => null,
					],
				],
			]
		);
	}

	public function testPassingOnWomanWithoutBeard() {
		Assert::same(
			[
				'general' => ['gender' => 'woman'],
				'face' => [
					'beard' => [
						'color_id' => null,
						'care' => null,
					],
				],
			],
			(new Constraint\DescriptionRule())->apply(
				[
					'general' => ['gender' => 'woman'],
					'face' => [
						'beard' => [
							'color_id' => null,
							'care' => null,
						],
					],
				]
			)
		);
	}

	public function testPassingManWithOrWithoutBeard() {
		Assert::noError(
			function() {
				(new Constraint\DescriptionRule())->apply(
					[
						'general' => ['gender' => 'man'],
						'face' => [
							'beard' => [
								'color_id' => null,
								'care' => null,
							],
						],
					]
				);
			}
		);
		Assert::noError(
			function() {
				(new Constraint\DescriptionRule())->apply(
					[
						'general' => ['gender' => 'man'],
						'face' => [
							'beard' => [
								'color_id' => 1,
								'care' => 10,
							],
						],
					]
				);
			}
		);
	}
}
(new DescriptionRuleTest())->run();