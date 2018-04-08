<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Dataset;

/**
 * Filter obey rules by JSON schema
 */
final class SchemaFilter extends Dataset\Filter {
	private $origin;
	private $schema;

	public function __construct(Dataset\Filter $origin, \SplFileInfo $schema) {
		$this->origin = $origin;
		$this->schema = $schema;
	}

	protected function filter(): array {
		$filter = $this->origin->filter();
		foreach ($this->properties($this->schema, $filter) as $property => $rules)
			$this->applyEnums($property, $rules, $filter);
		return $filter;
	}

	private function properties(\SplFileInfo $schema, array $filter): array {
		return array_intersect_key(
			json_decode(file_get_contents($schema->getPathname()), true)['properties'],
			$filter
		);
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
