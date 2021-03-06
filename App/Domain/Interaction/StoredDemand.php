<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends;
use Klapuch\Output;
use Klapuch\Sql;
use Klapuch\Storage;

final class StoredDemand implements Demand {
	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(int $id, Storage\Connection $connection) {
		$this->id = $id;
		$this->connection = $connection;
	}

	public function print(Output\Format $format): Output\Format {
		$demand = (new Storage\TypedQuery(
			$this->connection,
			(new FindMyFriends\Sql\IndividualDemands\Select())
				->from(['collective_demands'])
				->where('id = ?')
				->sql(),
			[$this->id]
		))->row();
		return (new CompleteDescription($format, $demand))
			->with('id', $demand['id'])
			->with('seeker_id', $demand['seeker_id'])
			->with('created_at', $demand['created_at'])
			->with('note', $demand['note']);
	}

	public function retract(): void {
		(new Storage\NativeQuery(
			$this->connection,
			'DELETE FROM demands WHERE id = ?',
			[$this->id]
		))->execute();
	}

	public function reconsider(array $description): void {
		(new Storage\BuiltQuery(
			$this->connection,
			(new FindMyFriends\Sql\IndividualDemands\Set(
				new Sql\AnsiUpdate('collective_demands'),
				$description
			))->where('id = :id', ['id' => $this->id])
		))->execute();
	}
}
