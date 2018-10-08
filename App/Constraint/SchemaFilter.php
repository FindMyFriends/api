<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
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

	/**
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	protected function filter(): array {
		return (new Dataset\ForbiddenSelection(
			new Dataset\FakeSelection(
				$this->withoutRest(
					$this->schema($this->schema),
					$this->origin->filter()
				)
			),
			$this->forbiddenCriteria
		))->criteria();
	}

	/**
	 * @param \SplFileInfo $file
	 * @throws \UnexpectedValueException
	 * @return mixed[]
	 */
	private function schema(\SplFileInfo $file): array {
		$content = @file_get_contents($file->getPathname());
		if ($content === false)
			throw new \UnexpectedValueException(sprintf('Schema "%s" is not readable', $file->getPathname()));
		$schema = json_decode($content, true);
		recursive_unset($schema, 'additionalProperties');
		recursive_unset($schema, 'required');
		$validator = new Validator();
		$values = (object) $this->origin->filter();
		$validator->validate($values, $schema, Constraint::CHECK_MODE_COERCE_TYPES);
		if (!$validator->isValid())
			throw new \UnexpectedValueException($validator->getErrors()[0]['message']);
		return $schema;
	}

	private function withoutRest(array $schema, array $filter): array {
		return array_intersect_key($filter, array_intersect_key($schema['properties'], $filter));
	}
}
