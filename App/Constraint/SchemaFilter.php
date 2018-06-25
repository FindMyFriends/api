<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Dataset;

/**
 * Filter obey rules by JSON schema
 */
final class SchemaFilter extends Dataset\Filter {
	/** @var \Klapuch\Dataset\Filter */
	private $origin;

	/** @var \SplFileInfo */
	private $schema;

	/** @var mixed[] */
	private $forbiddenCriteria;

	public function __construct(
		Dataset\Filter $origin,
		\SplFileInfo $schema,
		array $forbiddenCriteria = []
	) {
		$this->origin = $origin;
		$this->schema = $schema;
		$this->forbiddenCriteria = $forbiddenCriteria;
	}

	protected function filter(): array {
		$properties = $this->properties($this->schema, $this->origin->filter());
		return (new Dataset\ForbiddenSelection(
			new Dataset\FakeSelection(
				$this->applications(
					$properties,
					$this->withoutRest($properties, $this->origin->filter())
				)
			),
			$this->forbiddenCriteria
		))->criteria();
	}

	private function applications(array $properties, array $filter): array {
		foreach ($properties as $property => $rules)
			$this->applyEnums($property, $rules, $filter);
		return $filter;
	}

	private function properties(\SplFileInfo $schema, array $filter): array {
		$content = @file_get_contents($schema->getPathname());
		if ($content === false)
			throw new \UnexpectedValueException(sprintf('Schema "%s" is not readable', $schema->getPathname()));
		return array_intersect_key(
			json_decode($content, true)['properties'],
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

	private function withoutRest(array $properties, array $filter): array {
		return array_intersect_key($filter, $properties);
	}
}
