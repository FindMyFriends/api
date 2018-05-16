<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use FindMyFriends\Domain\Access;
use Predis;

/**
 * Rate limited entrance to specific calls in particular time
 */
final class RateLimitedEntrance implements Access\Entrance {
	private const KEY_FORMAT = 'seeker_rate_limit:%s';
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

	public function enter(array $credentials): Seeker {
		$seeker = $this->origin->enter($credentials);
		if (!$this->redis->exists($this->key($seeker))) {
			$this->redis->setex($this->key($seeker), self::TIME_LIMIT, 0);
		}
		if ($this->redis->incr($this->key($seeker)) >= self::CALLS_LIMIT) {
			throw new \UnexpectedValueException('You have overstepped rate limit');
		}
		return $seeker;
	}

	public function exit(): Seeker {
		return $this->origin->exit();
	}

	private function key(Seeker $seeker): string {
		return sprintf(self::KEY_FORMAT, $seeker->id());
	}
}
