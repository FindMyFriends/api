<?php
declare(strict_types = 1);
namespace FindMyFriends\Constraint;

use Klapuch\Storage;
use Klapuch\Validation;

/**
 * Rule for birth year
 */
final class BirthYearRule implements Validation\Rule {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function satisfied($subject): bool {
		return (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT birth_year_in_range(?)',
			[$subject]
		))->field();
	}

	public function apply($subject): string {
		if ($this->satisfied($subject))
			return $subject;
		throw new \UnexpectedValueException(
			sprintf('Birth year must be in range from 1850 to %d', date('Y'))
		);
	}
}