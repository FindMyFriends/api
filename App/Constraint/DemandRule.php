<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for demand
 */
final class DemandRule implements Validation\Rule {
	/**
	 * @param mixed[] $subject
	 * @return bool
	 */
	public function satisfied($subject): bool {
		return false; // not used
	}

	/**
	 * @param mixed[] $subject
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function apply($subject): array {
		return array_replace_recursive(
			[
				'general' => [
					'age' => (new AgeRangeRule())->apply($subject['general']['age']),
				],
			],
			(new DescriptionRule())->apply($subject)
		);
	}
}
