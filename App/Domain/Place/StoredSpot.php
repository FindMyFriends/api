<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use FindMyFriends\Sql;
use Klapuch\Output;
use Klapuch\Sql\AnsiUpdate;
use Klapuch\Storage;

/**
 * Spot stored in connection
 */
final class StoredSpot implements Spot {
	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(int $id, Storage\Connection $connection) {
		$this->id = $id;
		$this->connection = $connection;
	}

	public function forget(): void {
		// not needed yet
	}

	public function print(Output\Format $format): Output\Format {
		return $format; // not needed yet
	}

	public function move(array $movement): void {
		(new Storage\BuiltQuery(
			$this->connection,
			(new Sql\Spot\Set(new AnsiUpdate('spots'), $movement))
				->where('id = :id', ['id' => $this->id])
		))->execute();
	}
}
