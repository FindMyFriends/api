<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Schema;

use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class TableEnumTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testKeyPairedValues() {
		$this->database->exec('TRUNCATE body_builds CASCADE');
		$this->database->exec('ALTER SEQUENCE body_builds_id_seq RESTART');
		$this->database->exec("INSERT INTO body_builds (name) VALUES ('muscular')");
		$this->database->exec("INSERT INTO body_builds (name) VALUES ('skinny')");
		Assert::same(
			[
				1 => 'muscular',
				2 => 'skinny',
			],
			(new Schema\TableEnum('body_builds', $this->database))->values()
		);
	}
}

(new TableEnumTest())->run();
