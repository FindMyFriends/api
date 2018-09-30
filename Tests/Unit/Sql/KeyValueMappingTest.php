<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Sql;

use FindMyFriends\Sql;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class KeyValueMappingTest extends TestCase\Runtime {
	public function testMappingOnlyDesiredToApplication(): void {
		Assert::same(
			['_lastname' => 'yyy', '_name' => 'xxx'],
			(new Sql\KeyValueMapping(
				[
					'name' => '_name',
					'lastname' => '_lastname',
					'foo' => 'bar',
				]
			))->application(['name' => 'xxx', 'lastname' => 'yyy'])
		);
	}

	public function testMappingOnlyDesiredToDatabase(): void {
		Assert::same(
			['lastname' => 'yyy', 'name' => 'xxx'],
			(new Sql\KeyValueMapping(
				[
					'name' => '_name',
					'lastname' => '_lastname',
					'foo' => 'bar',
				]
			))->database(['_name' => 'xxx', '_lastname' => 'yyy'])
		);
	}

	public function testKeepingOrder(): void {
		$mapping = [
			'general_age' => 'general.age',
			'general_ethnic_group_id' => 'general.ethnic_group_id',
			'general_firstname' => 'general.firstname',
			'general_lastname' => 'general.lastname',
			'general_sex' => 'general.sex',
		];
		Assert::same(
			[
				'general.firstname' => 2,
				'general.lastname' => 3,
				'general.sex' => 1,
			],
			(new Sql\KeyValueMapping($mapping))->application([
				'general_sex' => 1,
				'general_lastname' => 3,
				'general_firstname' => 2,
			])
		);
		Assert::same(
			[
				'general_firstname' => 2,
				'general_lastname' => 3,
				'general_sex' => 1,
			],
			(new Sql\KeyValueMapping($mapping))->database([
				'general.sex' => 1,
				'general.lastname' => 3,
				'general.firstname' => 2,
			])
		);
	}
}

(new KeyValueMappingTest())->run();
