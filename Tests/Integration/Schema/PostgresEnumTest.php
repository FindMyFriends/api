<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Schema;

use FindMyFriends\Schema;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class PostgresEnumTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testValuesFromEnum(): void {
		Assert::same(
			['pending', 'processing', 'succeed', 'failed'],
			(new Schema\PostgresEnum('job_statuses', $this->connection))->values()
		);
	}
}

(new PostgresEnumTest())->run();
