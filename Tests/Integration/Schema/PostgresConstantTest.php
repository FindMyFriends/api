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
final class PostgresConstantTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testValuesFromEnum(): void {
		Assert::same(
			['man', 'woman'],
			(new Schema\PostgresConstant('sex', $this->connection))->values()
		);
	}
}

(new PostgresConstantTest())->run();
