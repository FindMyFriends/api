<?php
declare(strict_types = 1);
namespace FindMyFriends\Request;

use JsonSchema;
use Klapuch\Application;
use Klapuch\Output;

/**
 * Structured JSON request by schema
 */
final class StructuredJsonRequest implements Application\Request {
	private $origin;
	private $schema;

	public function __construct(Application\Request $origin, \SplFileInfo $schema) {
		$this->origin = $origin;
		$this->schema = $schema;
	}

	public function body(): Output\Format {
		$validator = new JsonSchema\Validator();
		$json = json_decode($this->origin->body()->serialization());
		$validator->validate(
			$json,
			['$ref' => 'file://' . $this->schema->getRealPath()],
			JsonSchema\Constraints\Constraint::CHECK_MODE_APPLY_DEFAULTS
		);
		if ($validator->isValid())
			return new Output\Json(json_decode(json_encode($json), true));
		throw new \UnexpectedValueException($this->error($validator));
	}

	public function headers(): array {
		return $this->origin->headers();
	}

	private function error(JsonSchema\Validator $validator): string {
		$error = current($validator->getErrors());
		if (strpos($error['property'], '.') === false)
			return $error['message'];
		return sprintf('%s (%s)', $error['message'], $error['property']);
	}
}