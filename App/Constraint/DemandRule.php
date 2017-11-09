<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Rule for demand
 */
final class DemandRule implements Validation\Rule {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function satisfied($subject): bool {
		return false; // not used
	}

	public function apply($subject): array {
		return array_replace_recursive(
			[
				'general' => [
					'birth_year' => (new BirthYearRule(
						$this->database
					))->apply($subject['general']['birth_year']),
				],
				'location' => [
					'met_at' => (new Validation\ChainedRule(
						new Validation\FriendlyRule(
							new OpenClosedRange(),
							'For location.met_at is only allowed open/closed range'
						),
						new Validation\FriendlyRule(
							new DateTimeRange(),
							'For location.met_at is only allowed datetime range'
						)
					))->apply($subject['location']['met_at']),
				],
			],
			$subject
		);
	}
}