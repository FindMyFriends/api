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

	/** @var \FindMyFriends\Domain\Access\Entrance */
	private $origin;

	/** @var \Predis\ClientInterface */
	private $redis;

	public function __construct(
		Access\Entrance $origin,
		Predis\ClientInterface $redis
	) {
		$this->origin = $origin;
		$this->redis = $redis;
	}

	/**
	 * @param array $credentials
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function enter(array $credentials): Seeker {
		$seeker = $this->origin->enter($credentials);
		['role' => $role] = $seeker->properties();
		if ($role === 'guest')
			return $seeker;
		if ($this->redis->exists($this->key($seeker)) === 0)
			$this->redis->setex($this->key($seeker), self::TIME_LIMIT, 0);
		if ($this->redis->incr($this->key($seeker)) >= self::CALLS_LIMIT)
			throw new \UnexpectedValueException('You have overstepped rate limit');
		return $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function exit(): Seeker {
		return $this->origin->exit();
	}

	private function key(Seeker $seeker): string {
		return sprintf(self::KEY_FORMAT, $seeker->id());
	}
}
