<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Schema;

use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class TableEnumTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testKeyPairedValues() {
		$this->connection->exec('TRUNCATE body_builds CASCADE');
		$this->connection->exec('ALTER SEQUENCE body_builds_id_seq RESTART');
		$this->connection->exec("INSERT INTO body_builds (name) VALUES ('muscular')");
		$this->connection->exec("INSERT INTO body_builds (name) VALUES ('skinny')");
		Assert::same(
			[
				1 => ['id' => 1, 'name' => 'muscular'],
				2 => ['id' => 2, 'name' => 'skinny'],
			],
			(new Schema\TableEnum('body_builds', $this->connection))->values()
		);
	}
}

(new TableEnumTest())->run();
