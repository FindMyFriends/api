<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Range in format as (10,20), [10,20], ("2017-01-01","2017-01-02")
 */
final class OpenClosedRange implements Validation\Rule {
	public function satisfied($subject): bool {
		return (bool) preg_match('~^[\(\)\[\]].+[\(\)\[\]]$~', $subject);
	}

	public function apply($subject): string {
		if (!$this->satisfied($subject))
			throw new \UnexpectedValueException('Only open/closed ranges are allowed');
		return $subject;
	}
}