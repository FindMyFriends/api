<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for demand
 */
final class DemandRule implements Validation\Rule {
	public function satisfied($subject): bool {
		return false; // not used
	}

	public function apply($subject): array {
		return array_replace_recursive(
			[
				'location' => [
					'met_at' => [
						'from' => (new Validation\FriendlyRule(
							new DateTimeRule(),
							'location.met_at.from must be in ISO8601'
						))->apply($subject['location']['met_at']['from']),
						'to' => (new Validation\FriendlyRule(
							new DateTimeRule(),
							'location.met_at.to must be in ISO8601'
						))->apply($subject['location']['met_at']['to']),
					],
				],
			],
			$subject
		);
	}
}