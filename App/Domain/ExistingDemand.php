<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Output;
use Klapuch\Storage;

final class ExistingDemand implements Demand {
	private $origin;
	private $id;
	private $database;

	public function __construct(Demand $origin, int $id, \PDO $database) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
	}

	public function print(Output\Format $format): Output\Format {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	public function retract(): void {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		$this->origin->retract();
	}

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

	private function exception(int $id): \Throwable {
		return new \UnexpectedValueException(
			'Demand does not exist',
			0,
			new \UnexpectedValueException(sprintf('Demand %d does not exist', $id))
		);
	}
}