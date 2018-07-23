<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use Klapuch\Routing;

/**
 * HashId chosen by comparing source with paths
 */
final class SuitedHashIdMask implements Routing\Mask {
	/** @var string[] */
	private $types;

	/** @var \Klapuch\Routing\Mask */
	private $origin;

	/** @var mixed[] */
	private $hashids;

	public function __construct(array $types, Routing\Mask $origin, array $hashids) {
		$this->types = $types;
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function parameters(): array {
		$parameters = $this->origin->parameters();
		return array_combine(
			array_keys($parameters),
			array_map(
				function(string $type, $value) {
					return isset($this->types[$type])
						? $this->cast($this->types[$type], $value)
						: $value;
				},
				array_keys($parameters),
				$parameters
			)
		);
	}

	/**
	 * @param string $type
	 * @param mixed $value
	 * @throws \UnexpectedValueException
	 * @return mixed
	 */
	private function cast(string $type, $value) {
		if (preg_match('~^hashid-(?P<type>\w+)$~', $type, $matches) === 1 && isset($this->hashids[$matches['type']])) {
			$decode = current($this->hashids[$matches['type']]->decode($value));
			if ($decode === false)
				throw new \UnexpectedValueException(sprintf('Parameter "%s" is not valid', $value));
			return $decode;
		}
		return $value;
	}

}
