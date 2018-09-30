<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Place;
use Klapuch\Storage;

/**
 * Spots owned by one particular seeker
 */
final class OwnedSpots implements Place\Spots {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	/** @var int */
	private $demand;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Place\Spots */
	private $origin;

	public function __construct(
		Place\Spots $origin,
		Access\Seeker $owner,
		int $demand,
		Storage\Connection $connection
	) {
		$this->origin = $origin;
		$this->owner = $owner;
		$this->demand = $demand;
		$this->connection = $connection;
	}

	/**
	 * @param mixed[] $spot
	 * @throws \UnexpectedValueException
	 */
	public function track(array $spot): void {
		if (!$this->owned($this->demand, $this->owner))
			throw $this->exception($this->demand);
		$this->origin->track($spot);
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
			$this->connection,
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
