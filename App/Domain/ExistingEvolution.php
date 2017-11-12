<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Always existing evolution
 */
final class ExistingEvolution implements Evolution {
	private $origin;
	private $id;
	private $database;

	public function __construct(Evolution $origin, int $id, \PDO $database) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
	}

	public function change(array $changes): void {
		if (!$this->exists($this->id))
			throw new \UnexpectedValueException(sprintf('Evolution %d does not exist', $this->id));
		$this->origin->change($changes);
	}

	public function print(Output\Format $format): Output\Format {
		if (!$this->exists($this->id))
			throw new \UnexpectedValueException(sprintf('Evolution %d does not exist', $this->id));
		return $this->origin->print($format);
	}

	private function exists(int $id): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1 FROM evolutions WHERE id = ?',
			[$id]
		))->field();
	}
}