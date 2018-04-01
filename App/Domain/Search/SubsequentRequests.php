<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Sql;
use Klapuch\Storage;

/**
 * Request bind to the most first request
 */
final class SubsequentRequests implements Requests {
	private $demand;
	private $database;

	public function __construct(int $demand, Storage\MetaPDO $database) {
		$this->demand = $demand;
		$this->database = $database;
	}

	public function refresh(string $status, ?int $self = null): int {
		return (new Storage\TypedQuery(
			$this->database,
			'INSERT INTO soulmate_requests (demand_id, status, self_id)
			VALUES (?, ?, ?)
			RETURNING COALESCE(self_id, id)',
			[$this->demand, $status, $self]
		))->field();
	}

	public function all(Dataset\Selection $selection): \Iterator {
		$requests = (new Storage\BuiltQuery(
			$this->database,
			new Dataset\SelectiveClause(
				(new Sql\AnsiSelect(['id', 'searched_at', 'status', 'is_soulmate_request_repeatable(searched_at) AS is_repeatable']))
					->from(['soulmate_requests'])
					->where('demand_id = :demand_id', [$this->demand]),
				$selection
			)
		))->rows();
		foreach ($requests as $request) {
			yield new class ($request) implements Request {
				private $request;

				public function __construct(array $request) {
					$this->request = $request;
				}

				public function print(Output\Format $format): Output\Format {
					return new Output\FilledFormat($format, $this->request);
				}
			};
		}
	}

	public function count(Dataset\Selection $selection): int {
		return (new Storage\BuiltQuery(
			$this->database,
			new Dataset\SelectiveClause(
				(new Sql\AnsiSelect(['COUNT(*)']))
					->from(['soulmate_requests'])
					->where('demand_id = :demand_id', ['demand_id' => $this->demand]),
				$selection
			)
		))->field();
	}
}