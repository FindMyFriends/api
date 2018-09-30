<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Always existing spot to deal with
 */
final class ExistingSpot implements Spot {
	/** @var \FindMyFriends\Domain\Place\Spot */
	private $origin;

	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Spot $origin, int $id, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->id = $id;
		$this->connection = $connection;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function forget(): void {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		$this->origin->forget();
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
	 * @param mixed[] $movement
	 * @throws \UnexpectedValueException
	 */
	public function move(array $movement): void {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		$this->origin->move($movement);
	}

	private function exists(int $id): bool {
		return (bool) (new Storage\NativeQuery(
			$this->connection,
			'SELECT 1 FROM spots WHERE id = ?',
			[$id]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Spot does not exist',
			0,
			new \UnexpectedValueException(sprintf('Spot %d does not exist', $id))
		);
	}
}
