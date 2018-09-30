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
final class CachedEnumTest extends TestCase\Runtime {
	use TestCase\Redis;

	public function testPersistenceInCache(): void {
		$origin = \Mockery::mock(Schema\Enum::class);
		$origin->shouldReceive('values')->once()->andReturn(['a', 'b', 'c']);
		$enum = new Schema\CachedEnum($origin, $this->redis, 'sex', 'enum');
		Assert::falsey($this->redis->exists('sex-enum'));
		Assert::same(['a', 'b', 'c'], $enum->values());
		Assert::same(['a', 'b', 'c'], $enum->values());
		Assert::same(['a', 'b', 'c'], igbinary_unserialize($this->redis->get('postgres:type:meta:enums:enum:sex')));
	}

	public function testPersistingEnumToInfinite(): void {
		$origin = \Mockery::mock(Schema\Enum::class);
		$origin->shouldReceive('values');
		(new Schema\CachedEnum($origin, $this->redis, 'sex', 'enum'))->values();
		Assert::same(-1, $this->redis->ttl('postgres:type:meta:enums:enum:sex'));
	}

	public function testPersistingTableForHour(): void {
		$origin = \Mockery::mock(Schema\Enum::class);
		$origin->shouldReceive('values');
		(new Schema\CachedEnum($origin, $this->redis, 'sex', 'table'))->values();
		Assert::same(3600, $this->redis->ttl('postgres:type:meta:enums:table:sex'));
	}

	protected function tearDown(): void {
		parent::tearDown();
		\Mockery::close();
	}
}

(new CachedEnumTest())->run();
