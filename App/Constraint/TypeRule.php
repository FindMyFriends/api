<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Validation;

/**
 * Values with given types
 */
final class TypeRule implements Validation\Rule {
	private $schema;

	public function __construct(\SplFileInfo $schema) {
		$this->schema = $schema;
	}

	public function satisfied($subject): bool {
		try {
			$this->apply($subject);
			return true;
		} catch (\UnexpectedValueException $e) {
			return false;
		}
	}

	public function apply($subject): array {
		$properties = json_decode(file_get_contents($this->schema->getPathname()), true)['properties'];
		foreach (array_intersect_key($properties, $subject) as $property => $rules) {
			$this->applyEnums($property, $rules, $subject);
		}
		return $subject;
	}

	private function applyEnums(string $property, array $rules, array $subject): void {
		if (isset($rules['enum']) && !in_array($subject[$property], $rules['enum'], true)) {
			throw new \UnexpectedValueException(
				sprintf(
					'\'%s\' must be one of: %s - \'%s\' was given',
					$property,
					implode(
						', ',
						array_map(
							function (string $value): string {
								return sprintf("'%s'", $value);
							},
							$rules['enum']
						)
					),
					$subject[$property]
				)
			);
		}
	}
}