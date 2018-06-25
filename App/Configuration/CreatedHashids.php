<?php
declare(strict_types = 1);

namespace FindMyFriends\Configuration;

use Hashids\Hashids;
use Klapuch\Configuration;

/**
 * Each hashid instantiated to Hashids class
 */
final class CreatedHashids implements Configuration\Source {
	/** @var \Klapuch\Configuration\Source */
	private $origin;

	public function __construct(Configuration\Source $origin) {
		$this->origin = $origin;
	}

	public function read(): array {
		$hashids = $this->origin->read();
		return array_reduce(
			array_keys($hashids),
			function(array $creations, string $name) use ($hashids): array {
				$creations[$name] = array_replace(
					$hashids[$name],
					[
						'hashid' => new Hashids(
							$hashids[$name]['hashid']['salt'],
							$hashids[$name]['hashid']['length']
						),
					]
				) + array_diff_key($hashids[$name], array_flip(['salt', 'length']));
				return $creations;
			},
			[]
		);
	}
}
