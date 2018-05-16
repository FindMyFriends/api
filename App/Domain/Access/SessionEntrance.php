<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Internal;

/**
 * Entrance representing HTTP session
 */
final class SessionEntrance implements Entrance {
	private $origin;
	private $session;
	private $extension;

	public function __construct(
		Entrance $origin,
		array &$session,
		Internal\Extension $extension
	) {
		$this->origin = $origin;
		$this->session = &$session;
		$this->extension = $extension;
	}

	public function enter(array $credentials): Seeker {
		$seeker = $this->origin->enter($credentials);
		if (session_status() !== PHP_SESSION_NONE)
			session_regenerate_id(true);
		$this->extension->improve();
		$this->session[self::IDENTIFIER] = $seeker->id();
		return $seeker;
	}

	public function exit(): Seeker {
		if (!isset($this->session[self::IDENTIFIER]))
			throw new \UnexpectedValueException('You are not logged in');
		unset($this->session[self::IDENTIFIER]);
		return $this->origin->exit();
	}
}
