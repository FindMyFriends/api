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
		if ($this->womanWithBeard($subject['general']['sex'], $subject['beard']))
			throw new \UnexpectedValueException('Women do not have beards');
		elseif ($this->manWithBreast($subject['general']['sex'], $subject['body']['breast_size']))
			throw new \UnexpectedValueException('Breast is valid only for women');
		return array_replace_recursive(
			[
				'body' => [
					'height' => (new Validation\FriendlyRule(
						new ValueWithUnitRule(),
						'Body height is missing value or unit.'
					))->apply($subject['body']['height']),
					'weight' => (new Validation\FriendlyRule(
						new ValueWithUnitRule(),
						'Body weight is missing value or unit.'
					))->apply($subject['body']['weight']),
				],
				'hair' => [
					'length' => (new Validation\FriendlyRule(
						new ValueWithUnitRule(),
						'Hair length is missing value or unit.'
					))->apply($subject['hair']['length']),
				],
				'beard' => [
					'length' => (new Validation\FriendlyRule(
						new ValueWithUnitRule(),
						'Beard length is missing value or unit.'
					))->apply($subject['beard']['length']),
				],
				'hands' => [
					'nails' => [
						'length' => (new Validation\FriendlyRule(
							new ValueWithUnitRule(),
							'Nails length is missing value or unit.'
						))->apply($subject['hands']['nails']['length']),
					],
				],
			],
			$subject
		);
	}

	private function womanWithBeard(string $sex, array $beard): bool {
		return $sex === 'woman' && count(array_unique(array_values_recursive($beard))) > 1;
	}

	private function manWithBreast(string $sex, ?string $breast): bool {
		return $sex === 'man' && $breast !== null;
	}
}
