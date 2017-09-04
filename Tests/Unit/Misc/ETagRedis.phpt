<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace FindMyFriends\Unit\Misc;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ETagRedis extends \Tester\TestCase {
	use TestCase\Redis;

	public function testSection() {
		static $key = '/books/1';
		$redis = new Misc\ETagRedis($this->redis);
		Assert::same(0, $redis->exists($key));
		$redis->set($key, 'abc');
		Assert::same('abc', $redis->get($key));
		Assert::null($this->redis->get($key));
		Assert::same(0, $this->redis->exists($key));
	}
}

(new ETagRedis())->run();