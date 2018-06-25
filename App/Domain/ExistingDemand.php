<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Output;
use Klapuch\Storage;

final class ExistingDemand implements Demand {
	/** @var \FindMyFriends\Domain\Demand */
	private $origin;

	/** @var int */
	private $id;

	/** @var \PDO */
	private $database;

	public function __construct(Demand $origin, int $id, \PDO $database) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function retract(): void {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		$this->origin->retract();
	}

	/**
	 * @param array $description
	 * @throws \UnexpectedValueException
	 */
	public function reconsider(array $description): void {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		$this->origin->reconsider($description);
	}

	private function exists(int $id): bool {
		return (bool) (new Storage\NativeQuery(
			$this->database,
			'SELECT 1 FROM demands WHERE id = ?',
			[$id]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Demand does not exist',
			0,
			new \UnexpectedValueException(sprintf('Demand %d does not exist', $id))
		);
	}
}
