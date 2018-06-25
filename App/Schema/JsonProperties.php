<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

/**
 * Properties gathered from JSON
 */
final class JsonProperties implements Properties {
	/** @var \SplFileInfo */
	private $schema;

	public function __construct(\SplFileInfo $schema) {
		$this->schema = $schema;
	}

	public function objects(): array {
		['properties' => $properties] = json_decode(
			file_get_contents($this->schema->getPathname()),
			true
		);
		return $properties;
	}
}
