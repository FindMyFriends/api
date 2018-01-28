<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Predis;

final class CachedEnum implements Enum {
	private const NAMESPACE = 'postgres:type:meta:enums:';
	private const TTL = [
		'enum' => [],
		'table' => ['ex', 3600],
	];
	private $origin;
	private $redis;
	private $field;
	private $type;

	public function __construct(Enum $origin, Predis\ClientInterface $redis, string $field, string $type) {
		$this->origin = $origin;
		$this->redis = $redis;
		$this->field = $field;
		$this->type = $type;
	}

	public function values(): array {
		if (!$this->redis->exists($this->key($this->field, $this->type)))
			$this->redis->set($this->key($this->field, $this->type), json_encode($this->origin->values()), ...self::TTL[$this->type]);
		return json_decode($this->redis->get($this->key($this->field, $this->type)), true);
	}

	private function key(string $field, string $type): string {
		return sprintf('%s:%s', self::NAMESPACE . $type, $field);
	}
}