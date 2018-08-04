<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use FindMyFriends\Domain\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Spot which belongs only to me
 */
final class OwnedSpot implements Spot {
	/** @var \FindMyFriends\Domain\Place\Spot */
	private $origin;

	/** @var int */
	private $id;

	/** @var \PDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	public function __construct(
		Spot $origin,
		int $id,
		Access\Seeker $owner,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
		$this->owner = $owner;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function forget(): void {
		if (!$this->owned($this->id, $this->owner))
			throw $this->exception($this->id);
		$this->origin->forget();
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if (!$this->owned($this->id, $this->owner))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	private function owned(int $id, Access\Seeker $owner): bool {
		return (new Storage\NativeQuery(
			$this->database,
			'SELECT is_spot_owned(:spot, :seeker)',
			['spot' => $id, 'seeker' => $owner->id()]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Spot does not belong to you',
			0,
			new \UnexpectedValueException(sprintf('Spot %d does not belong to you.', $id))
		);
	}
}
