<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for description
 */
final class DescriptionRule implements Validation\Rule {
	public function satisfied($subject): bool {
		return false; // not used
	}

	public function apply($subject): array {
		if ($this->womanWithBeard($subject['general']['gender'], $subject['beard']))
			throw new \UnexpectedValueException('Women do not have beards');
		elseif ($this->manWithBreast($subject['general']['gender'], $subject['body']['breast_size']))
			throw new \UnexpectedValueException('Breast is valid only for women');
		return array_replace_recursive(
			[
				'body' => [
					'height' => (new ValueWithUnitRule(
						'body.height'
					))->apply($subject['body']['height']),
					'weight' => (new ValueWithUnitRule(
						'body.weight'
					))->apply($subject['body']['weight']),
				],
				'hair' => [
					'length' => (new ValueWithUnitRule(
						'hair.length'
					))->apply($subject['hair']['length']),
				],
				'beard' => [
					'length' => (new ValueWithUnitRule(
						'beard.length'
					))->apply($subject['beard']['length']),
				],
				'hands' => [
					'nails' => [
						'length' => (new ValueWithUnitRule(
							'hands.nails.length'
						))->apply($subject['hands']['nails']['length']),
					],
				],
			],
			$subject
		);
	}

	private function womanWithBeard(string $gender, array $beard): bool {
		return $gender === 'woman' && current(array_unique(array_values_recursive($beard))) !== null;
	}

	private function manWithBreast(string $gender, ?string $breast): bool {
		return $gender === 'man' && $breast !== null;
	}
}