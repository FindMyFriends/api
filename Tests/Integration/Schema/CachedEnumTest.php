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

final class CachedEnumTest extends Tester\TestCase {
	use TestCase\Redis;

	public function testPersistenceInCache() {
		$origin = \Mockery::mock(Schema\Enum::class);
		$origin->shouldReceive('values')->once()->andReturn(['a', 'b', 'c']);
		$enum = new Schema\CachedEnum($origin, $this->redis, 'genders', 'enum');
		Assert::falsey($this->redis->exists('genders-enum'));
		Assert::same(['a', 'b', 'c'], $enum->values());
		Assert::same(['a', 'b', 'c'], $enum->values());
		Assert::same(['a', 'b', 'c'], json_decode($this->redis->get('postgres:type:meta:enums:enum:genders')));
	}

	public function testPersistingEnumToInfinite() {
		$origin = \Mockery::mock(Schema\Enum::class);
		$origin->shouldReceive('values');
		(new Schema\CachedEnum($origin, $this->redis, 'genders', 'enum'))->values();
		Assert::same(-1, $this->redis->ttl('postgres:type:meta:enums:enum:genders'));
	}

	public function testPersistingTableForHour() {
		$origin = \Mockery::mock(Schema\Enum::class);
		$origin->shouldReceive('values');
		(new Schema\CachedEnum($origin, $this->redis, 'genders', 'table'))->values();
		Assert::same(3600, $this->redis->ttl('postgres:type:meta:enums:table:genders'));
	}

	protected function tearDown() {
		parent::tearDown();
		\Mockery::close();
	}
}

(new CachedEnumTest())->run();