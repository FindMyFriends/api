<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;
use Tester\Assert;

final class TableCounts implements Assertion {
	private $database;
	private $counts;

	public function __construct(\PDO $database, array $counts) {
		$this->database = $database;
		$this->counts = $counts;
	}

	public function assert(): void {
		Assert::same(
			[],
			(new Storage\TypedQuery(
				$this->database,
				'SELECT test_utils.tables_not_matching_count(test_utils.json_to_hstore(?))',
				[json_encode($this->counts)]
			))->field()
		);
	}
}