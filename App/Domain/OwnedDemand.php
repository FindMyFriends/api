<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Demand which belongs only to me
 */
final class OwnedDemand implements Demand {
	private $origin;
	private $id;
	private $database;
	private $owner;

	public function __construct(
		Demand $origin,
		int $id,
		Access\User $owner,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
		$this->owner = $owner;
	}

	public function print(Output\Format $format): Output\Format {
		if (!$this->owned($this->id))
			throw new \UnexpectedValueException(sprintf('%d is not your demand', $this->id));
		return $this->origin->print($format);
	}

	public function retract(): void {
		if (!$this->owned($this->id))
			throw new \UnexpectedValueException(sprintf('%d is not your demand', $this->id));
		$this->origin->retract();
	}

	public function reconsider(array $description): void {
		if (!$this->owned($this->id))
			throw new \UnexpectedValueException(sprintf('%d is not your demand', $this->id));
		$this->origin->reconsider($description);
	}

	private function owned(int $id): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM demands
			WHERE id = ?
			AND seeker_id = ?',
			[$id, $this->owner->id()]
		))->field();
	}
}