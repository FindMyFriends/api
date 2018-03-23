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
			if ($this->matching($this->source, $choice['paths'])) {
				return array_reduce(
					array_keys($choice['parameters']),
					function (array $parameters, string $field) use ($choice): array {
						$parameters += array_filter(
							(new Routing\HashIdMask(
								$this->origin,
								[$field],
								$this->hashids[$choice['parameters'][$field]]['hashid']
							))->parameters(),
							'is_int'
						);
						return $parameters;
					},
					[]
				) + $this->origin->parameters();
			}
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