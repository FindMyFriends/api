<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule date time in ISO8601 format
 */
final class DateTimeRule implements Validation\Rule {
	/**
	 * @param string $subject
	 * @return bool
	 */
	public function satisfied($subject): bool {
		return $subject === (new \DateTime($subject))->format(\DateTime::ATOM)
			|| $subject === (new \DateTime($subject))->format(\DateTime::RFC3339_EXTENDED);
	}

	/**
	 * @param string $subject
	 * @throws \UnexpectedValueException
	 * @return string
	 */
	public function apply($subject): string {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException('Datetime must be in ISO8601');
	}
}
