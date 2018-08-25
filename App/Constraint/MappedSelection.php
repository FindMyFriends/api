<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Dataset;

/**
 * Selection mapped to DB columns
 */
final class MappedSelection implements Dataset\Selection {
	/** @var \Klapuch\Dataset\Selection */
	private $origin;

	public function __construct(Dataset\Selection $origin) {
		$this->origin = $origin;
	}

	public function criteria(): array {
		return array_combine(
			array_keys($this->origin->criteria()),
			array_reduce(
				$this->origin->criteria(),
				static function (array $mapping, array $criteria): array {
					$mapping[] = array_combine(
						array_map(
							static function (string $key): string {
								return str_replace('.', '_', $key);
							},
							array_keys($criteria)
						),
						$criteria
					);
					return $mapping;
				},
				[]
			)
		);
	}
}
