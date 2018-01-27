<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

final class CachedEnum implements Enum {
	private const TTL = [
		'enum' => 0,
		'table' => 3600,
	];
	private $origin;
	private $field;
	private $type;

	public function __construct(Enum $origin, string $field, string $type) {
		$this->origin = $origin;
		$this->field = $field;
		$this->type = $type;
	}

	public function values(): array {
		if (!apcu_exists($this->key($this->field, $this->type)))
			apcu_store($this->key($this->field, $this->type), $this->origin->values(), self::TTL[$this->type]);
		return apcu_fetch($this->key($this->field, $this->type));
	}

	private function key(string $field, string $type): string {
		return sprintf('%s-%s', $field, $type);
	}
}