<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule date time in ISO8601 format
 */
final class DateTimeRule implements Validation\Rule {
	private $property;

	public function __construct(string $property) {
		$this->property = $property;
	}

	public function satisfied($subject): bool {
		return $subject === (new \DateTime($subject))->format(\DateTime::ATOM);
	}

	public function apply($subject): string {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException(
			sprintf('%s - datetime must be in ISO8601', $this->property)
		);
	}
}