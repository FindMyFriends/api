<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use JsonSchema;
use Klapuch\Validation;

/**
 * JSON structured by schema
 * NOTE: Ruined by JsonSchema library :(
 */
final class StructuredJson implements Validation\Rule {
	/** @var \SplFileInfo */
	private $schema;

	public function __construct(\SplFileInfo $schema) {
		$this->schema = $schema;
	}

	/**
	 * @param mixed $subject
	 * @return bool
	 */
	public function satisfied($subject): bool {
		return $this->validator($this->forValidation($subject))->isValid();
	}

	/**
	 * @param array|\stdClass $subject
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function apply($subject): array {
		$json = $this->forValidation($subject);
		$validator = $this->validator($json);
		if ($validator->isValid())
			return $this->forOutput($json);
		throw new \UnexpectedValueException($this->error($validator));
	}

	private function validator(\stdClass $json): JsonSchema\Validator {
		$validator = new JsonSchema\Validator();
		$validator->validate(
			$json,
			['$ref' => 'file://' . $this->schema->getRealPath()]
		);
		return $validator;
	}

	private function error(JsonSchema\Validator $validator): string {
		$error = current($validator->getErrors());
		if (strpos($error['property'], '.') === false)
			return $error['message'];
		return sprintf('%s (%s)', $error['message'], $error['property']);
	}

	/**
	 * @param mixed $subject
	 * @return \stdClass
	 */
	private function forValidation($subject): \stdClass {
		return (object) json_decode(json_encode($subject));
	}

	/**
	 * @param mixed $subject
	 * @return array
	 */
	private function forOutput($subject): array {
		return json_decode(json_encode($subject), true);
	}
}
