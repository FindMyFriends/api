<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

use Predis;

final class CachedEnum implements Enum {
	private const NAMESPACE = 'postgres:type:meta:enums:';
	private const TTL = [
		'enum' => [],
		'constant' => [],
		'table' => ['ex', 3600],
	];

	/** @var \FindMyFriends\Schema\Enum */
	private $origin;

	/** @var \Predis\ClientInterface */
	private $redis;

	/** @var string */
	private $field;

	/** @var string */
	private $type;

	public function __construct(Enum $origin, Predis\ClientInterface $redis, string $field, string $type) {
		$this->origin = $origin;
		$this->redis = $redis;
		$this->field = $field;
		$this->type = $type;
	}

	public function values(): array {
		if ($this->redis->exists($this->key($this->field, $this->type)) === 0)
			$this->redis->set($this->key($this->field, $this->type), igbinary_serialize($this->origin->values()), ...self::TTL[$this->type]);
		return igbinary_unserialize($this->redis->get($this->key($this->field, $this->type)));
	}

	private function key(string $field, string $type): string {
		return sprintf('%s:%s', self::NAMESPACE . $type, $field);
	}
}
