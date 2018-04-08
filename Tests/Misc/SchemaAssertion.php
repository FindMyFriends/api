<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use JsonSchema;
use Tester\Assert;

final class SchemaAssertion implements Assertion {
	private $values;
	private $schema;

	/**
	 * @param \stdClass|array $values Copying typehint from JsonSchema\Validator::validate method
	 * @param \SplFileInfo $schema
	 */
	public function __construct($values, \SplFileInfo $schema) {
		$this->values = $values;
		$this->schema = $schema;
	}

	public function assert(): void {
		if (is_array($this->values)) {
			foreach ($this->values as $value) {
				(new self($value, $this->schema))->assert();
			}
		} else {
			$validator = new JsonSchema\Validator();
			$validator->validate(
				$this->values,
				['$ref' => 'file://' . $this->schema->getRealPath()]
			);
			Assert::true(
				$validator->isValid(),
				sprintf('%s: %s', current($validator->getErrors())['message'], current($validator->getErrors())['property'])
			);
		}
	}
}
