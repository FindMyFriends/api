<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Age with "from" and "to" as range
 */
final class AgeRangeRule implements Validation\Rule {
	public function satisfied($subject): bool {
		return $subject['from'] <= $subject['to'];
	}

	public function apply($subject): array {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException('Age must be properly ordered as range');
	}
}
