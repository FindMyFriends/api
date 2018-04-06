<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Storage;
use Klapuch\Validation;

/**
 * Values with given types
 */
final class TypeRule implements Validation\Rule {
	private $database;
	private $types;

	public function __construct(Storage\MetaPDO $database, array $types) {
		$this->database = $database;
		$this->types = $types;
	}

	public function satisfied($subject): bool {
		return (new Storage\TypedQuery(
			$this->database,
			'SELECT is_enum_value(?::json, ?::json)',
			[json_encode($this->types), json_encode($subject)]
		))->field();
	}

	public function apply($subject): array {
		(new Storage\ApplicationQuery(
			new Storage\TypedQuery(
				$this->database,
				'SELECT check_enum_value(?::json, ?::json)',
				[json_encode($this->types), json_encode($subject)]
			)
		))->execute();
		return $subject;
	}
}