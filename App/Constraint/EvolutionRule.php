<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for evolution
 */
final class EvolutionRule implements Validation\Rule {
	public function satisfied($subject): bool {
		return false; // not used
	}

	public function apply($subject): array {
		return array_replace_recursive(
			[
				'evolved_at' => (new Validation\FriendlyRule(
					new DateTimeRule(),
					'evolved_at must be in ISO8601'
				))->apply($subject['evolved_at']),
			],
			(new DescriptionRule())->apply($subject)
		);
	}
}