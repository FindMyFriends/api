<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Storage;

/**
 * Locations owned by one particular seeker
 */
final class OwnedLocations implements Place\Locations {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	/** @var int */
	private $demand;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Place\Locations */
	private $origin;

	public function __construct(
		Place\Locations $origin,
		Access\Seeker $owner,
		int $demand,
		Storage\MetaPDO $database
	) {
		$this->origin = $origin;
		$this->owner = $owner;
		$this->demand = $demand;
		$this->database = $database;
	}

	/**
	 * @param mixed[] $location
	 * @throws \UnexpectedValueException
	 */
	public function track(array $location): void {
		if (!$this->owned($this->demand, $this->owner))
			throw $this->exception($this->demand);
		$this->origin->track($location);
	}

	/**
	 * @return \Iterator
	 * @throws \UnexpectedValueException
	 */
	public function history(): \Iterator {
		if (!$this->owned($this->demand, $this->owner))
			throw $this->exception($this->demand);
		return $this->origin->history();
	}

	private function owned(int $change, Access\Seeker $owner): bool {
		return (new Storage\NativeQuery(
			$this->database,
			'SELECT is_demand_owned(:demand, :seeker)',
			['demand' => $change, 'seeker' => $owner->id()]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Demand does not belong to you.',
			0,
			new \UnexpectedValueException(sprintf('Demand %d does not belong to you.', $id))
		);
	}
}
