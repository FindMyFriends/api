<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Place;
use Klapuch\Storage;

/**
 * Locations owned by one particular seeker
 */
final class OwnedLocations implements Place\Locations {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	/** @var int */
	private $change;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Place\Locations */
	private $origin;

	public function __construct(
		Place\Locations $origin,
		Access\Seeker $owner,
		int $change,
		Storage\MetaPDO $database
	) {
		$this->origin = $origin;
		$this->owner = $owner;
		$this->change = $change;
		$this->database = $database;
	}

	/**
	 * @param mixed[] $location
	 * @throws \UnexpectedValueException
	 */
	public function track(array $location): void {
		if (!$this->owned($this->change, $this->owner))
			throw $this->exception($this->change);
		$this->origin->track($location);
	}

	/**
	 * @return \Iterator
	 * @throws \UnexpectedValueException
	 */
	public function history(): \Iterator {
		if (!$this->owned($this->change, $this->owner))
			throw $this->exception($this->change);
		return $this->origin->history();
	}

	private function owned(int $change, Access\Seeker $owner): bool {
		return (new Storage\NativeQuery(
			$this->database,
			'SELECT is_evolution_owned(:evolution, :seeker)',
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
