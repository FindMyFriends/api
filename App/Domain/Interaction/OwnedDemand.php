<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends\Domain\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Demand which belongs only to me
 */
final class OwnedDemand implements Demand {
	/** @var \FindMyFriends\Domain\Interaction\Demand */
	private $origin;

	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	public function __construct(
		Demand $origin,
		int $id,
		Access\Seeker $owner,
		Storage\Connection $connection
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->connection = $connection;
		$this->owner = $owner;
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if (!$this->owned($this->id))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function retract(): void {
		if (!$this->owned($this->id))
			throw $this->exception($this->id);
		$this->origin->retract();
	}

	/**
	 * @param array $description
	 * @throws \UnexpectedValueException
	 */
	public function reconsider(array $description): void {
		if (!$this->owned($this->id))
			throw $this->exception($this->id);
		$this->origin->reconsider($description);
	}

	private function owned(int $id): bool {
		return (bool) (new Storage\NativeQuery(
			$this->connection,
			'SELECT is_demand_owned(?::integer, ?::integer)',
			[$id, $this->owner->id()]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'This is not your demand',
			0,
			new \UnexpectedValueException(sprintf('%d is not your demand', $id))
		);
	}
}
