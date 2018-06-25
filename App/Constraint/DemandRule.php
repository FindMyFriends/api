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
	 * @return array
	 */
	public function apply($subject): array {
		if ($subject['location']['met_at']['timeline_side'] === 'exactly' && $subject['location']['met_at']['approximation'] !== null) {
			throw new \UnexpectedValueException(
				'Exactly timeline side does not have approximation.'
			);
		}
		return array_replace_recursive(
			[
				'general' => [
					'age' => (new AgeRangeRule())->apply($subject['general']['age']),
				],
				'location' => [
					'met_at' => [
						'moment' => (new Validation\FriendlyRule(
							new DateTimeRule(),
							'Met at moment is not a valid datetime.'
						))->apply($subject['location']['met_at']['moment']),
						'approximation' => (new IfNotNullRule(
							new Validation\FriendlyRule(
								new IntervalDiffRule('P2D'),
								'Overstepped maximum of 2 days as approximated met at interval.'
							)
						))->apply(
							(new IfNotNullRule(
								new Validation\FriendlyRule(
									new IntervalRule(),
									'Approximation is not a valid interval.'
								)
							))->apply($subject['location']['met_at']['approximation'])
						),
					],
				],
			],
			(new DescriptionRule())->apply($subject)
		);
	}
}
