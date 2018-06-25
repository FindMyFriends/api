<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Length with unit and value
 */
final class ValueWithUnitRule implements Validation\Rule {
	/**
	 * @param mixed[] $subject
	 * @return bool
	 */
	public function satisfied($subject): bool {
		return in_array(count(array_filter($subject)), [0, 2], true);
	}

	/**
	 * @param mixed[] $subject
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function apply($subject): array {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException('Filled value must have unit and vice versa');
	}
}
