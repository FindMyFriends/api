<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Range in format as (10,20), [10,20]
 */
final class IntRange implements Validation\Rule {
	public function satisfied($subject): bool {
		[$begin, $end] = explode(',', trim($subject, '[()]'), 2);
		return filter_var($begin ?: 0, FILTER_VALIDATE_INT) !== false
			&& filter_var($end ?: 0, FILTER_VALIDATE_INT) !== false;
	}

	public function apply($subject): string {
		if (!$this->satisfied($subject))
			throw new \UnexpectedValueException('Range must be numeric');
		return $subject;
	}
}