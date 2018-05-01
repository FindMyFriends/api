<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

/**
 * Properties separated with dot
 */
final class NestedProperties implements Properties {
	private $origin;

	public function __construct(Properties $origin) {
		$this->origin = $origin;
	}

	public function objects(): array {
		return $this->nestedProperties($this->origin->objects());
	}

	private function nestedProperties(array $properties): array {
		return array_reduce(
			array_keys($properties),
			function (array $nested, string $property) use ($properties): array {
				$nested[] = $this->format($property, $properties);
				return $nested;
			},
			[]
		);
	}

	private function format(string $parent, array $childrens): string {
		if (isset($childrens[$parent]['properties'])) {
			return sprintf(
				'%s.%s',
				$parent,
				implode('.', $this->nestedProperties($childrens[$parent]['properties']))
			);
		}
		return $parent;
	}
}
