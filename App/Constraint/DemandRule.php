<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for demand
 */
final class DemandRule implements Validation\Rule {
	public function satisfied($subject): bool {
		return (new OpenClosedInterval())->satisfied($subject['general']['age']);
	}

	public function apply($subject): array {
		return array_replace_recursive(
			[
				'general' => [
					'age' => (new Validation\FriendlyRule(
						new OpenClosedInterval(),
						'For general.age is only allowed open/closed numeric interval'
					))->apply($subject['general']['age']),
				],
			],
			$subject
		);
	}
}