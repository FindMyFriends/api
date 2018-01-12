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
				'body' => ['breast_size' => null],
				'general' => ['gender' => 'woman'],
				'beard' => [
					'color_id' => 8,
					'care' => null,
				],
			]
		);
	}

	/**
	 * @throws \UnexpectedValueException Breast is valid only for women
	 */
	public function testThrowingOnManWithBreast() {
		(new Constraint\DescriptionRule())->apply(
			[
				'beard' => [],
				'general' => ['gender' => 'man'],
				'body' => [
					'breast_size' => 'B',
				],
			]
		);
	}

	public function testPassingOnWomanWithoutBeard() {
		Assert::same(
			[
				'body' => ['breast_size' => null],
				'general' => ['gender' => 'woman'],
				'beard' => [
					'color_id' => null,
					'care' => null,
				],
			],
			(new Constraint\DescriptionRule())->apply(
				[
					'body' => ['breast_size' => null],
					'general' => ['gender' => 'woman'],
					'beard' => [
						'color_id' => null,
						'care' => null,
					],
				]
			)
		);
	}

	public function testPassingOnWomanWithBreast() {
		Assert::same(
			[
				'body' => ['breast_size' => 'B'],
				'general' => ['gender' => 'woman'],
				'beard' => [
					'color_id' => null,
					'care' => null,
				],
			],
			(new Constraint\DescriptionRule())->apply(
				[
					'body' => ['breast_size' => 'B'],
					'general' => ['gender' => 'woman'],
					'beard' => [
						'color_id' => null,
						'care' => null,
					],
				]
			)
		);
	}
}
(new DescriptionRuleTest())->run();