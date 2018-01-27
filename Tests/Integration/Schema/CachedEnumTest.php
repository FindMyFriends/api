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
	use TestCase\Mockery;

	public function testPersistenceInApcu() {
		$origin = $this->mock(Schema\Enum::class);
		$origin->shouldReceive('values')->once()->andReturn(['a', 'b', 'c']);
		$enum = new Schema\CachedEnum($origin, 'genders', 'enum');
		Assert::false(apcu_fetch('genders'));
		Assert::same(['a', 'b', 'c'], $enum->values());
		Assert::same(['a', 'b', 'c'], $enum->values());
		Assert::same(['a', 'b', 'c'], apcu_fetch('genders-enum'));
	}

	public function testPersistingEnumToInfinite() {
		$origin = $this->mock(Schema\Enum::class);
		$origin->shouldReceive('values');
		(new Schema\CachedEnum($origin, 'genders', 'enum'))->values();
		['ttl' => $ttl, 'info' => $info] = apcu_cache_info()['cache_list'][0];
		Assert::same(0, $ttl);
		Assert::same('genders-enum', $info);
	}

	public function testPersistingTableForHour() {
		$origin = $this->mock(Schema\Enum::class);
		$origin->shouldReceive('values');
		(new Schema\CachedEnum($origin, 'genders', 'table'))->values();
		['ttl' => $ttl, 'info' => $info] = apcu_cache_info()['cache_list'][0];
		Assert::same(3600, $ttl);
		Assert::same('genders-table', $info);
	}
}

(new CachedEnumTest())->run();