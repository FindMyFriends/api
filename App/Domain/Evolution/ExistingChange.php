<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Change which belongs always to some chain
 */
final class ExistingChange implements Change {
	private $origin;
	private $id;
	private $database;

	public function __construct(Change $origin, int $id, \PDO $database) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
	}

	public function affect(array $changes): void {
		if (!$this->exists($this->id))
			throw new \UnexpectedValueException(sprintf('Evolution %d does not exist', $this->id));
		$this->origin->affect($changes);
	}

	public function print(Output\Format $format): Output\Format {
		if (!$this->exists($this->id))
			throw new \UnexpectedValueException(sprintf('Evolution %d does not exist', $this->id));
		return $this->origin->print($format);
	}

	public function revert(): void {
		if (!$this->exists($this->id))
			throw new \UnexpectedValueException(sprintf('Evolution %d does not exist', $this->id));
		$this->origin->revert();
	}

	private function exists(int $id): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1 FROM evolutions WHERE id = ?',
			[$id]
		))->field();
	}
}