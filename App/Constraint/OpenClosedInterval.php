<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Dasuos\Internal\Condition;
use Dasuos\Internal\Iteration;
use Klapuch\Validation;

/**
 * Interval in format as "(10,20)", "[10,20]"
 */
final class OpenClosedInterval implements Validation\Rule {
	public function satisfied($subject): bool {
		[$left, $right] = [substr($subject, 0, 1), substr($subject, -1)];
		$ranges = (new Iteration\Filtered(
			new Iteration\Hash(explode(',', trim($subject, $left . $right))),
			new Condition\Callback('is_numeric')
		))->product();
		return !array_diff([$left, $right], ['(', ')', '[', ']']) && count($ranges) === 2;
	}

	public function apply($subject): string {
		if (!$this->satisfied($subject))
			throw new \UnexpectedValueException('Allowed only open/closed numeric intervals');
		return $subject;
	}
}