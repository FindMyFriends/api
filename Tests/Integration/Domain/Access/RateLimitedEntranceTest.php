<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Access\RateLimitedEntrance;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class RateLimitedEntranceTest extends TestCase\Runtime {
	use TestCase\Redis;

	/**
	 * @throws \UnexpectedValueException You have overstepped rate limit
	 */
	public function testThrowingOnOversteppedLimit(): void {
		$id = '1';
		$this->redis->set(sprintf('seeker_rate_limit:%s', $id), 200);
		(new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker($id, ['role' => 'member'])),
			$this->redis
		))->enter([]);
	}

	public function testPassingOnMultipleCalls(): void {
		$entrance = new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker('1', ['role' => 'member'])),
			$this->redis
		);
		Assert::noError(static function () use ($entrance) {
			$entrance->enter([]);
			$entrance->enter([]);
		});
	}

	public function testIncrementingByCalls(): void {
		$id = '1';
		$this->redis->set(sprintf('seeker_rate_limit:%s', $id), 10);
		$entrance = new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker($id, ['role' => 'member'])),
			$this->redis
		);
		$entrance->enter([]);
		Assert::same('11', $this->redis->get(sprintf('seeker_rate_limit:%s', $id)));
		$entrance->enter([]);
		Assert::same('12', $this->redis->get(sprintf('seeker_rate_limit:%s', $id)));
	}

	public function testEnabledExpiration(): void {
		$id = '1';
		Assert::falsey($this->redis->exists(sprintf('seeker_rate_limit:%s', $id)));
		Assert::notSame(-1, $this->redis->ttl(sprintf('seeker_rate_limit:%s', $id)));
		(new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker($id, ['role' => 'member'])),
			$this->redis
		))->enter([]);
		Assert::notSame(-1, $this->redis->ttl(sprintf('seeker_rate_limit:%s', $id)));
	}

	public function testMultipleCallWithoutOverwritingTime(): void {
		[$id, $time] = ['1', 3];
		$this->redis->setex(sprintf('seeker_rate_limit:%s', $id), $time, 10);
		$entrance = new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker($id, ['role' => 'member'])),
			$this->redis
		);
		$entrance->enter([]);
		Assert::true($this->redis->ttl(sprintf('seeker_rate_limit:%s', $id)) <= $time);
	}

	public function testDisabledIncrementForGuest(): void {
		$id = '1';
		$this->redis->set(sprintf('seeker_rate_limit:%s', $id), 0);
		$entrance = new RateLimitedEntrance(
			new Access\FakeEntrance(new Access\FakeSeeker($id, ['role' => 'guest'])),
			$this->redis
		);
		$entrance->enter([]);
		$entrance->enter([]);
		Assert::same('0', $this->redis->get(sprintf('seeker_rate_limit:%s', $id)));
	}
}

(new RateLimitedEntranceTest())->run();
