<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Internal;
use Klapuch\Output;

/**
 * Simplified mass printing to JSON
 */
final class JsonPrintedObjects implements Output\Format {
	/** @var object[] */
	private $prints;

	public function __construct(object ...$prints) {
		$this->prints = $prints;
	}

	public function serialization(): string {
		return (new Internal\EncodedJson(
			array_reduce(
				$this->prints,
				static function(array $objects, object $object): array {
					$objects[] = json_decode($object->print(new Output\Json())->serialization(), true);
					return $objects;
				},
				[]
			),
			JSON_PRETTY_PRINT
		))->value();
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
		return new Output\Json(
			array_map(
				static function(Output\Format $format): array {
					return json_decode($format->serialization(), true);
				},
				array_reduce(
					$this->prints,
					static function(array $objects, object $object) use ($tag, $adjustment): array {
						$objects[] = $object->print(new Output\Json())->adjusted($tag, $adjustment);
						return $objects;
					},
					[]
				)
			)
		);
	}
}
