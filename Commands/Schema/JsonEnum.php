<?php
declare(strict_types = 1);

namespace FindMyFriends\Commands\Schema;

final class JsonEnum implements Enum {
	private $origin;
	private $property;
	private $old;
	private $new;

	public function __construct(Enum $origin, array $property, string $old, string $new) {
		$this->origin = $origin;
		$this->property = $property;
		$this->old = $old;
		$this->new = $new;
	}

	public function values(): array {
		unset($this->property['properties'][$this->old]);
		$this->property['properties'] += [
			$this->new => [
				'type' => ['integer', 'null'],
				'enum' => array_merge([null], $this->origin->values()),
			],
		];
		unset($this->property['required'][array_search($this->old, $this->property['required'], true)]);
		$this->property['required'] = array_merge($this->property['required'], [$this->new]);
		return $this->property;
	}
}