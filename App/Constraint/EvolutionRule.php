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
		if (isset($subject['general']['age'])) {
			$subject['general']['birth_year'] = [
				'from' => (new \DateTime($subject['evolved_at']))->format('Y') - $subject['general']['age']['to'],
				'to' => (new \DateTime($subject['evolved_at']))->format('Y') - $subject['general']['age']['from'],
			];
			unset($subject['general']['age']);
		}
		return array_replace_recursive(
			[
				'evolved_at' => (new Validation\FriendlyRule(
					new DateTimeRule(),
					'evolved_at must be in ISO8601'
				))->apply($subject['evolved_at']),
			],
			$subject
		);
	}
}