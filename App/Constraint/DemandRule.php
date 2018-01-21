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
		if ($subject['location']['met_at']['timeline_side'] === 'exactly' && $subject['location']['met_at']['approximation'] !== null) {
			throw new \UnexpectedValueException(
				'location.met_at.timeline_side - "exactly" do not have approximation'
			);
		}
		return array_replace_recursive(
			[
				'location' => [
					'met_at' => [
						'moment' => (new Validation\FriendlyRule(
							new DateTimeRule(),
							'location.met_at.moment must be in ISO8601'
						))->apply($subject['location']['met_at']['moment']),
						'approximation' => (new Validation\FriendlyRule(
							new IntervalRule(),
							'location.met_at.approximation must be in ISO8601'
						))->apply($subject['location']['met_at']['approximation']),
					],
				],
			],
			(new DescriptionRule())->apply($subject)
		);
	}
}