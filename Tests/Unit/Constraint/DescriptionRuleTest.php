<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class DescriptionRuleTest extends Tester\TestCase {
	private const BASE = [
		'body' => [
			'breast_size' => null,
		],
		'general' => ['sex' => 'woman'],
		'beard' => [
			'color_id' => null,
		],
	];

	public function testThrowingOnWomanWithBeard() {
		Assert::exception(static function() {
			(new Constraint\DescriptionRule())->apply(
				array_replace_recursive(
					self::BASE,
					[
						'general' => ['sex' => 'woman'],
						'beard' => [
							'color_id' => 8,
							'care' => null,
						],
					]
				)
			);
		}, \UnexpectedValueException::class, 'Women do not have beards');
		Assert::exception(static function() {
			(new Constraint\DescriptionRule())->apply(
				array_replace_recursive(
					self::BASE,
					[
						'general' => ['sex' => 'woman'],
						'beard' => [
							'style_id' => 1,
							'color_id' => null,
						],
					]
				)
			);
		}, \UnexpectedValueException::class, 'Women do not have beards');
	}

	/**
	 * @throws \UnexpectedValueException Breast is valid only for women
	 */
	public function testThrowingOnManWithBreast() {
		(new Constraint\DescriptionRule())->apply(
			array_replace_recursive(
				self::BASE,
				[
					'general' => ['sex' => 'man'],
					'body' => [
						'breast_size' => 'B',
					],
				]
			)
		);
	}

	public function testPassingOnWomanWithoutBeard() {
		$expectation = array_replace_recursive(
			self::BASE,
			[
				'general' => ['sex' => 'woman'],
				'beard' => [
					'color_id' => null,
				],
			]
		);
		Assert::equal($expectation, (new Constraint\DescriptionRule())->apply($expectation));
	}

	public function testPassingOnWomanWithBreast() {
		$expectation = array_replace_recursive(
			self::BASE,
			[
				'body' => ['breast_size' => 'B'],
				'general' => ['sex' => 'woman'],
			]
		);
		Assert::equal($expectation, (new Constraint\DescriptionRule())->apply($expectation));
	}
}
(new DescriptionRuleTest())->run();
