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
		$validator = new JsonSchema\Validator();
		$validator->validate($this->values, $this->schema->getPathname());
		Assert::true($validator->isValid(), implode(' & ', $validator->getErrors()));
	}
}