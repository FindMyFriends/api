<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use Klapuch\Dataset;

/**
 * Cached demands
 */
final class CachedDemands implements Demands {
	/** @var int|null */
	private $count;

	/** @var \Iterator|null */
	private $all;

	/** @var \FindMyFriends\Domain\Interaction\Demands */
	private $origin;

	public function __construct(Demands $origin) {
		$this->origin = $origin;
	}

	/**
	 * @param array $description
	 * @throws \UnexpectedValueException
	 * @return int
	 */
	public function ask(array $description): int {
		return $this->origin->ask($description);
	}

	public function all(Dataset\Selection $selection): \Iterator {
		if ($this->all === null)
			$this->all = $this->origin->all($selection);
		return $this->all;
	}

	public function count(Dataset\Selection $selection): int {
		if ($this->count === null)
			$this->count = $this->origin->count($selection);
		return $this->count;
	}
}
