<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for evolution
 */
final class EvolutionRule implements Validation\Rule {
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
				'evolved_at' => (new Validation\FriendlyRule(
					new DateTimeRule(),
					'Evolved at is not a valid datetime.'
				))->apply($subject['evolved_at']),
			],
			(new DescriptionRule())->apply($subject)
		);
	}
}
