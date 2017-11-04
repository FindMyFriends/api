<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Http;

use FindMyFriends\Http;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ETagRedis extends Tester\TestCase {
	use TestCase\Redis;

	public function testSection() {
		static $key = '/books/1';
		$redis = new Http\ETagRedis($this->redis);
		Assert::same(0, $redis->exists($key));
		$redis->set($key, 'abc');
		Assert::same('abc', $redis->get($key));
		Assert::null($this->redis->get($key));
		Assert::same(0, $this->redis->exists($key));
	}
}

(new ETagRedis())->run();