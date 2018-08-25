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
	private const BASE = [
		'body' => [
			'breast_size' => null,
			'height' => ['value' => 10, 'unit' => 'mm'],
			'weight' => ['value' => 100, 'unit' => 'kg'],
		],
		'hair' => [
			'length' => ['value' => 10, 'unit' => 'mm'],
		],
		'general' => ['sex' => 'woman'],
		'beard' => [
			'color_id' => null,
			'length' => [
				'value' => null,
				'unit' => null,
			],
		],
		'hands' => [
			'nails' => [
				'length' => [
					'value' => null,
					'unit' => null,
				],
			],
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
							'style' => 'cool',
							'color_id' => null,
							'length' => [
								'value' => 2,
								'unit' => 'mm',
							],
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
					'care' => null,
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


	/**
	 * @dataProvider valuesWithoutUnits
	 */
	public function testThrowingOnValuesWithoutUnits(array $part) {
		$ex = Assert::exception(
			static function() use ($part) {
				(new Constraint\DescriptionRule())->apply(array_replace_recursive(self::BASE, $part));
			},
			\UnexpectedValueException::class
		);
		Assert::contains(' is missing value or unit.', $ex->getMessage());
	}

	protected function valuesWithoutUnits(): array {
		return [
			[['body' => ['height' => ['value' => null, 'unit' => 'mm']]]],
			[['body' => ['weight' => ['value' => null, 'unit' => 'mm']]]],
			[['hair' => ['length' => ['value' => null, 'unit' => 'mm']]]],
			[['beard' => ['length' => ['value' => null, 'unit' => 'mm']], 'general' => ['sex' => 'man']]],
			[['hands' => ['nails' => ['length' => ['value' => null, 'unit' => 'mm']]]]],
		];
	}
}
(new DescriptionRuleTest())->run();
