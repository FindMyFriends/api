<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;
use Tester\Assert;

final class TableCount implements Assertion {
	private $database;
	private $table;
	private $count;

	public function __construct(\PDO $database, string $table, int $count) {
		$this->database = $database;
		$this->table = $table;
		$this->count = $count;
	}

	public function assert(): void {
		Assert::same(
			$this->count,
			(new Storage\NativeQuery(
				$this->database,
				sprintf('SELECT COUNT(*) FROM %s', $this->table)
			))->field(),
			sprintf('%s TABLE', strtoupper($this->table))
		);
	}
}