<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;
use Tester\Assert;

final class TableCount implements Assertion {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var string */
	private $table;

	/** @var int */
	private $count;

	public function __construct(Storage\Connection $connection, string $table, int $count) {
		$this->connection = $connection;
		$this->table = $table;
		$this->count = $count;
	}

	public function assert(): void {
		Assert::same(
			$this->count,
			(new Storage\NativeQuery(
				$this->connection,
				sprintf('SELECT COUNT(*) FROM %s', $this->table)
			))->field(),
			sprintf('%s TABLE', strtoupper($this->table))
		);
	}
}
