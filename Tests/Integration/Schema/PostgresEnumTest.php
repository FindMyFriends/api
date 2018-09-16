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
final class PostgresEnumTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testValuesFromEnum() {
		Assert::same(
			['man', 'woman'],
			(new Schema\PostgresEnum('sex_enum', $this->database))->values()
		);
	}
}

(new PostgresEnumTest())->run();
