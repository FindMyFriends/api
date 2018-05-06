<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Access;
use Klapuch\Access\User;
use Predis;

/**
 * Rate limited entrance to specific calls in particular time
 */
final class RateLimitedEntrance implements Access\Entrance {
	private const KEY_FORMAT = 'user_rate_limit:%s';
	private const TIME_LIMIT = 15 * 60,
		CALLS_LIMIT = 180;
	private $origin;
	private $redis;

	public function __construct(
		Access\Entrance $origin,
		Predis\ClientInterface $redis
	) {
		$this->origin = $origin;
		$this->redis = $redis;
	}

	public function enter(array $credentials): User {
		$user = $this->origin->enter($credentials);
		if (!$this->redis->exists($this->key($user))) {
			$this->redis->setex($this->key($user), self::TIME_LIMIT, 0);
		}
		if ($this->redis->incr($this->key($user)) >= self::CALLS_LIMIT) {
			throw new \UnexpectedValueException('You have overstepped rate limit');
		}
		return $user;
	}

	public function exit(): User {
		return $this->origin->exit();
	}

	private function key(User $user): string {
		return sprintf(self::KEY_FORMAT, $user->id());
	}
}
