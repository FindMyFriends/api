<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;
use Tester\Assert;

final class TableCounts implements Assertion {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var mixed[] */
	private $counts;

	public function __construct(Storage\Connection $connection, array $counts) {
		$this->connection = $connection;
		$this->counts = $counts;
	}

	public function assert(): void {
		Assert::same(
			[],
			(new Storage\TypedQuery(
				$this->connection,
				'SELECT test_utils.tables_not_matching_count(test_utils.json_to_hstore(?))',
				[json_encode($this->counts)]
			))->field()
		);
	}
}
