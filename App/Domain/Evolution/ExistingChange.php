<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Change which belongs always to some chain
 */
final class ExistingChange implements Change {
	/** @var \FindMyFriends\Domain\Evolution\Change */
	private $origin;

	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Change $origin, int $id, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->id = $id;
		$this->connection = $connection;
	}

	/**
	 * @param array $changes
	 * @throws \UnexpectedValueException
	 */
	public function affect(array $changes): void {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		$this->origin->affect($changes);
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
	public function revert(): void {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		$this->origin->revert();
	}

	private function exists(int $id): bool {
		return (bool) (new Storage\NativeQuery(
			$this->connection,
			'SELECT 1 FROM evolutions WHERE id = ?',
			[$id]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Evolution change does not exist',
			0,
			new \UnexpectedValueException(sprintf('Evolution change %d does not exist', $id))
		);
	}
}
