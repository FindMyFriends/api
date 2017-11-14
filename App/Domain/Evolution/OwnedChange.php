<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

use Klapuch\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Evolution change which belongs only to me
 */
final class OwnedChange implements Change {
	private $origin;
	private $id;
	private $database;
	private $owner;

	public function __construct(
		Change $origin,
		int $id,
		Access\User $owner,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
		$this->owner = $owner;
	}

	public function affect(array $changes): void {
		if (!$this->owned($this->id))
			throw new \UnexpectedValueException(sprintf('%d is not your evolution change', $this->id));
		$this->origin->affect($changes);
	}

	public function revert(): void {
		if (!$this->owned($this->id))
			throw new \UnexpectedValueException(sprintf('%d is not your evolution change', $this->id));
		$this->origin->revert();
	}

	public function print(Output\Format $format): Output\Format {
		if (!$this->owned($this->id))
			throw new \UnexpectedValueException(sprintf('%d is not your evolution change', $this->id));
		return $this->origin->print($format);
	}

	private function owned(int $id): bool {
		return (bool) (new Storage\ParameterizedQuery(
			$this->database,
			'SELECT 1
			FROM evolutions
			WHERE id = ?
			AND seeker_id = ?',
			[$id, $this->owner->id()]
		))->field();
	}
}