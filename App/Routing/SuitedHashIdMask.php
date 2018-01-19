<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use Klapuch\Routing;

/**
 * HashId chosen by comparing source with paths
 */
final class SuitedHashIdMask implements Routing\Mask {
	private $origin;
	private $hashids;
	private $source;

	public function __construct(Routing\Mask $origin, array $hashids, string $source) {
		$this->origin = $origin;
		$this->hashids = $hashids;
		$this->source = $source;
	}

	public function parameters(): array {
		foreach ($this->hashids as $choice) {
			if ($this->matching($this->source, $choice['paths']))
				return (new Routing\HashIdMask($this->origin, ['id'], $choice['hashid']))->parameters();
		}
		return $this->origin->parameters();
	}

	private function matching(string $source, array $paths): bool {
		return (bool) array_filter(
			array_map(
				function(string $path) use ($source): int {
					return preg_match(sprintf('~%s~', $path), $source);
				},
				$paths
			)
		);
	}

}