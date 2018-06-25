<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use FindMyFriends\Schema;
use Klapuch\Dataset;

/**
 * Sort obey rules by JSON schema
 */
final class SchemaSort extends Dataset\Sort {
	/** @var \Klapuch\Dataset\Sort */
	private $origin;

	/** @var \SplFileInfo */
	private $schema;

	/** @var mixed[] */
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
				array_flip(
					(new Schema\NestedProperties(
						new Schema\JsonProperties($schema)
					))->objects()
				)
			)
		);
	}
}
