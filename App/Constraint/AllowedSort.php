<?php
declare(strict_types = 1);

namespace FindMyFriends\Constraint;

use Klapuch\Dataset;

/**
 * Only allowed sorts
 */
final class AllowedSort extends Dataset\Sort {
	private $origin;
	private $allowedSorts;

	public function __construct(Dataset\Sort $origin, array $allowedSorts) {
		$this->origin = $origin;
		$this->allowedSorts = $allowedSorts;
	}

	protected function sort(): array {
		if ($this->allowed($this->origin->sort(), $this->allowedSorts))
			return $this->origin->sort();
		throw new \UnexpectedValueException(
			sprintf(
				'Following sorts are not allowed: "%s"',
				implode(
					', ',
					$this->diff($this->origin->sort(), $this->allowedSorts)
				)
			)
		);
	}

	private function allowed(array $sorts, array $allowedSorts): bool {
		return empty($this->diff($sorts, $allowedSorts));
	}

	private function diff(array $sorts, array $allowedSorts): array {
		return array_keys(array_diff_ukey($sorts, array_flip($allowedSorts), 'strcasecmp'));
	}
}
