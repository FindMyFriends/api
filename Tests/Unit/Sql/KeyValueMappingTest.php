<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Sql;

use FindMyFriends\Sql;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class KeyValueMappingTest extends Tester\TestCase {
	public function testMappingOnlyDesiredToApplication() {
		Assert::same(
			['_name' => 'xxx', '_lastname' => 'yyy'],
			(new Sql\KeyValueMapping(
				[
					'name' => '_name',
					'lastname' => '_lastname',
					'foo' => 'bar',
				]
			))->application(['name' => 'xxx', 'lastname' => 'yyy'])
		);
	}

	public function testMappingOnlyDesiredToDatabase() {
		Assert::same(
			['name' => 'xxx', 'lastname' => 'yyy'],
			(new Sql\KeyValueMapping(
				[
					'name' => '_name',
					'lastname' => '_lastname',
					'foo' => 'bar',
				]
			))->database(['_name' => 'xxx', '_lastname' => 'yyy'])
		);
	}
}

(new KeyValueMappingTest())->run();
