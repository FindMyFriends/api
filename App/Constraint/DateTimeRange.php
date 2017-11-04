<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Range in format as (("2017-01-01","2017-01-02")), [("2017-01-01","2017-01-02")]
 */
final class DateTimeRange implements Validation\Rule {
	public function satisfied($subject): bool {
		[$begin, $end] = array_map(
			function(string $range): string {
				return trim($range, '"');
			},
			explode(',', trim($subject, '[()]'), 2)
		);
		try {
			return new \DateTime($begin) && new \DateTime($end);
		} catch (\Throwable $e) {
			return false;
		}
	}

	public function apply($subject): string {
		if (!$this->satisfied($subject))
			throw new \UnexpectedValueException('Range must be datetime');
		return $subject;
	}
}