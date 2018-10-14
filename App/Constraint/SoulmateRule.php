<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for soulmate
 */
final class SoulmateRule implements Validation\Rule {
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
		if (isset($subject['is_exposed']) && $subject['is_exposed'] === false)
			throw new \UnexpectedValueException('Property is_exposed is only possible to change to true');
		return $subject;
	}
}
