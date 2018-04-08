<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Compares intervals
 */
final class IntervalDiffRule implements Validation\Rule {
	private $max;

	public function __construct(string $max) {
		$this->max = $max;
	}

	public function satisfied($subject): bool {
		$now = new \DateTimeImmutable();
		return $now->add(new \DateInterval($subject)) <= $now->add(new \DateInterval($this->max));
	}

	public function apply($subject): string {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException(
			sprintf('Max diff is "%s", given "%s"', $this->max, $subject)
		);
	}
}
