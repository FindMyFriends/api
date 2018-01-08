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
		return $subject;
	}

	private function womanWithBeard(string $gender, array $beard): bool {
		return $gender === 'woman' && current(array_unique($beard)) !== null;
	}
}