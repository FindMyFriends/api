<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Place;
use Klapuch\Storage;

/**
 * Spots owned by one particular seeker
 */
final class VisibleSpots implements Place\Spots {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	/** @var int */
	private $change;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Place\Spots */
	private $origin;

	public function __construct(
		Place\Spots $origin,
		Access\Seeker $owner,
		int $change,
		Storage\Connection $connection
	) {
		$this->origin = $origin;
		$this->owner = $owner;
		$this->change = $change;
		$this->connection = $connection;
	}

	/**
	 * @param mixed[] $spot
	 * @throws \UnexpectedValueException
	 */
	public function track(array $spot): void {
		if (!$this->owned($this->change, $this->owner))
			throw $this->exception($this->change);
		$this->origin->track($spot);
	}

	/**
	 * @return \Iterator
	 * @throws \UnexpectedValueException
	 */
	public function history(): \Iterator {
		if (!$this->visible($this->change, $this->owner))
			throw $this->exception($this->change);
		return $this->origin->history();
	}

	private function owned(int $change, Access\Seeker $owner): bool {
		return (new Storage\NativeQuery(
			$this->connection,
			'SELECT is_evolution_owned(:evolution, :seeker)',
			['evolution' => $change, 'seeker' => $owner->id()]
		))->field();
	}

	private function visible(int $change, Access\Seeker $owner): bool {
		return (new Storage\NativeQuery(
			$this->connection,
			'SELECT is_evolution_visible(:evolution, :seeker)',
			['evolution' => $change, 'seeker' => $owner->id()]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Evolution change does not belong to you.',
			0,
			new \UnexpectedValueException(sprintf('Evolution change %d does not belong to you.', $id))
		);
	}
}
