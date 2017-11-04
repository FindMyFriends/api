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
					'birth_year' => (new Validation\ChainedRule(
						new Validation\FriendlyRule(
							new OpenClosedRange(),
							'For general.birth_year is only allowed open/closed range'
						),
						new Validation\FriendlyRule(
							new IntRange(),
							'For general.birth_year is only allowed numeric range'
						),
						new BirthYearRule($this->database)
					))->apply($subject['general']['birth_year']),
				],
			],
			$subject
		);
	}
}