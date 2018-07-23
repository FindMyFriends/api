<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Klapuch\Internal;

/**
 * Properties gathered from JSON
 */
final class JsonProperties implements Properties {
	/** @var \SplFileInfo */
	private $schema;

	public function __construct(\SplFileInfo $schema) {
		$this->schema = $schema;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function objects(): array {
		$content = @file_get_contents($this->schema->getPathname()); // @ escalated to exception
		if ($content === false)
			throw new \UnexpectedValueException('Schema can not be loaded');
		['properties' => $properties] = (new Internal\DecodedJson($content))->values();
		return $properties;
	}
}
