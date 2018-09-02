<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for description
 */
final class DescriptionRule implements Validation\Rule {
	/**
	 * @param mixed[] $subject
	 * @return bool
	 */
	public function satisfied($subject): bool {
		return false; // not used
	}

	/**
	 * @param mixed[] $subject
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function apply($subject): array {
		if ($this->womanWithBeard($subject['general']['sex'], $subject['beard']))
			throw new \UnexpectedValueException('Women do not have beards');
		elseif ($this->manWithBreast($subject['general']['sex'], $subject['body']['breast_size']))
			throw new \UnexpectedValueException('Breast is valid only for women');
		return $subject;
	}

	private function womanWithBeard(string $sex, array $beard): bool {
		return $sex === 'woman' && count(array_unique(array_values_recursive($beard))) > 1;
	}

	private function manWithBreast(string $sex, ?string $breast): bool {
		return $sex === 'man' && $breast !== null;
	}
}
