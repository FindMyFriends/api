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

final class PostgresEnumTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testValuesFromEnum() {
		Assert::same(
			['man', 'woman'],
			(new Schema\PostgresEnum('genders', $this->database))->values()
		);
	}
}

(new PostgresEnumTest())->run();