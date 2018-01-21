<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Length with unit and value
 */
final class ValueWithUnitRule implements Validation\Rule {
	private $property;

	public function __construct(string $property) {
		$this->property = $property;
	}

	public function satisfied($subject): bool {
		return in_array(count(array_filter($subject)), [0, 2], true);
	}

	public function apply($subject): array {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException(
			sprintf(
				'%s - filled value must have unit and vice versa',
				$this->property
			)
		);
	}
}