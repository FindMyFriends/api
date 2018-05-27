<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule date time in ISO8601 format
 */
final class DateTimeRule implements Validation\Rule {
	public function satisfied($subject): bool {
		return $subject === (new \DateTime($subject))->format(\DateTime::ATOM)
			|| $subject === (new \DateTime($subject))->format(\DateTime::RFC3339_EXTENDED);
	}

	public function apply($subject): string {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException('Datetime must be in ISO8601');
	}
}
