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
		'general' => ['gender' => 'woman'],
		'beard' => [
			'color_id' => null,
			'care' => null,
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

	/**
	 * @throws \UnexpectedValueException Women do not have beards
	 */
	public function testThrowingOnWomanWithBeard() {
		(new Constraint\DescriptionRule())->apply(
			array_replace_recursive(
				self::BASE,
				[
					'general' => ['gender' => 'woman'],
					'beard' => [
						'color_id' => 8,
						'care' => null,
					],
				]
			)
		);
	}

	/**
	 * @throws \UnexpectedValueException Breast is valid only for women
	 */
	public function testThrowingOnManWithBreast() {
		(new Constraint\DescriptionRule())->apply(
			array_replace_recursive(
				self::BASE,
				[
					'general' => ['gender' => 'man'],
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
				'general' => ['gender' => 'woman'],
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
				'general' => ['gender' => 'woman'],
			]
		);
		Assert::equal($expectation, (new Constraint\DescriptionRule())->apply($expectation));
	}


	/**
	 * @dataProvider valuesWithoutUnits
	 */
	public function testThrowingOnValuesWithoutUnits(array $part, string $property) {
		Assert::exception(
			function() use ($part) {
				(new Constraint\DescriptionRule())->apply(array_replace_recursive(self::BASE, $part));
			},
			\UnexpectedValueException::class,
			sprintf('%s - filled value must have unit and vice versa', $property)
		);
	}

	protected function valuesWithoutUnits(): array {
		return [
			[['body' => ['height' => ['value' => null, 'unit' => 'mm']]], 'body.height'],
			[['body' => ['weight' => ['value' => null, 'unit' => 'mm']]], 'body.weight'],
			[['hair' => ['length' => ['value' => null, 'unit' => 'mm']]], 'hair.length'],
			[['beard' => ['length' => ['value' => null, 'unit' => 'mm']], 'general' => ['gender' => 'man']], 'beard.length'],
			[['hands' => ['nails' => ['length' => ['value' => null, 'unit' => 'mm']]]], 'hands.nails.length'],
		];
	}
}
(new DescriptionRuleTest())->run();