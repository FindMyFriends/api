<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use FindMyFriends\Sql;
use Klapuch\Output;
use Klapuch\Sql\AnsiUpdate;
use Klapuch\Storage;

/**
 * Spot stored in database
 */
final class StoredSpot implements Spot {
	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(int $id, Storage\MetaPDO $database) {
		$this->id = $id;
		$this->database = $database;
	}

	public function forget(): void {
		// not needed yet
	}

	public function print(Output\Format $format): Output\Format {
		return $format; // not needed yet
	}

	public function move(array $movement): void {
		(new Storage\BuiltQuery(
			$this->database,
			(new Sql\Spot\Set(new AnsiUpdate('spots'), $movement))
				->where('id = :id', ['id' => $this->id])
		))->execute();
	}
}
