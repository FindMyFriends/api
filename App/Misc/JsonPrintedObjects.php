<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Output;

/**
 * Simplified mass printing to JSON
 */
final class JsonPrintedObjects implements Output\Format {
	private $prints;

	public function __construct(object ...$prints) {
		$this->prints = $prints;
	}

	public function serialization(): string {
		return json_encode(
			array_reduce(
				$this->prints,
				function(array $objects, object $object): array {
					$objects[] = json_decode($object->print(new Output\Json())->serialization(), true);
					return $objects;
				},
				[]
			),
			JSON_PRETTY_PRINT
		);
	}

	/**
	 * @param mixed $tag
	 * @param mixed $content
	 */
	public function with($tag, $content = null): Output\Format {
		throw new \Exception('Not implemented');
	}

	/**
	 * @param mixed $tag
	 * @param callable $adjustment
	 */
	public function adjusted($tag, callable $adjustment): Output\Format {
		throw new \Exception('Not implemented');
	}
}