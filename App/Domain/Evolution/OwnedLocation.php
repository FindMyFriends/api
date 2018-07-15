<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Location which belongs only to me
 */
final class OwnedLocation implements Location {
	/** @var \FindMyFriends\Domain\Evolution\Location */
	private $origin;

	/** @var int */
	private $id;

	/** @var \PDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	public function __construct(
		Location $origin,
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
			'SELECT is_location_owned(:location, :seeker)',
			['location' => $id, 'seeker' => $owner->id()]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Location does not belong to you',
			0,
			new \UnexpectedValueException(sprintf('Location %d does not belong to you.', $id))
		);
	}
}
