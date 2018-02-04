<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule interval in ISO8601 format
 */
final class IntervalRule implements Validation\Rule {
	public function satisfied($subject): bool {
		try {
			new \DateInterval($subject);
			return true;
		} catch (\Throwable $ex) {
			return false;
		}
	}

	public function apply($subject): string {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException('Interval must be in ISO8601');
	}
}