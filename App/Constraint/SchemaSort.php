<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Dataset;

/**
 * Sort obey rules by JSON schema
 */
final class SchemaSort extends Dataset\Sort {
	private $origin;
	private $schema;
	private $forbiddenCriteria;

	public function __construct(
		Dataset\Sort $origin,
		\SplFileInfo $schema,
		array $forbiddenCriteria = []
	) {
		$this->origin = $origin;
		$this->schema = $schema;
		$this->forbiddenCriteria = $forbiddenCriteria;
	}

	protected function sort(): array {
		return (new Dataset\ForbiddenSelection(
			new Dataset\FakeSelection($this->origin->sort()),
			array_merge(
				$this->properties($this->schema, $this->origin->sort()),
				$this->forbiddenCriteria
			)
		))->criteria();
	}

	private function properties(\SplFileInfo $schema, array $sort): array {
		return array_keys(
			array_diff_key(
				$sort,
				json_decode(
					file_get_contents($schema->getPathname()),
					true
				)['properties']
			)
		);
	}
}
