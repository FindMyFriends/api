<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access\RateLimitedEntrance;
use FindMyFriends\TestCase;
use Klapuch\Access;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class RateLimitedEntranceTest extends Tester\TestCase {
	use TestCase\Redis;

	/**
	 * @throws \UnexpectedValueException You have overstepped rate limit
	 */
	public function testThrowingOnOversteppedLimit() {
		$id = '1';
		$this->redis->set(sprintf('user_rate_limit:%s', $id), 200);
		(new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeUser($id)),
			$this->redis
		))->enter([]);
	}

	public function testPassingOnMultipleCalls() {
		$entrance = new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeUser('1')),
			$this->redis
		);
		Assert::noError(function () use ($entrance) {
			$entrance->enter([]);
			$entrance->enter([]);
		});
	}

	public function testIncrementingByCalls() {
		$id = '1';
		$this->redis->set(sprintf('user_rate_limit:%s', $id), 10);
		$entrance = new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeUser('1')),
			$this->redis
		);
		$entrance->enter([]);
		Assert::same('11', $this->redis->get(sprintf('user_rate_limit:%s', $id)));
		$entrance->enter([]);
		Assert::same('12', $this->redis->get(sprintf('user_rate_limit:%s', $id)));
	}

	public function testEnabledExpiration() {
		$id = '1';
		Assert::falsey($this->redis->exists(sprintf('user_rate_limit:%s', $id)));
		Assert::notSame(-1, $this->redis->ttl(sprintf('user_rate_limit:%s', $id)));
		(new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeUser($id)),
			$this->redis
		))->enter([]);
		Assert::notSame(-1, $this->redis->ttl(sprintf('user_rate_limit:%s', $id)));
	}

	public function testMultipleCallWithoutOverwritingTime() {
		[$id, $time] = ['1', 3];
		$this->redis->setex(sprintf('user_rate_limit:%s', $id), $time, 10);
		$entrance = new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeUser($id)),
			$this->redis
		);
		$entrance->enter([]);
		Assert::true($this->redis->ttl(sprintf('user_rate_limit:%s', $id)) <= $time);
	}
}

(new RateLimitedEntranceTest())->run();
