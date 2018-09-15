<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Seeker from session storage
 */
final class SessionSeeker implements Seeker {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $origin;

	public function __construct(Seeker $origin) {
		$this->origin = $origin;
	}

	public function id(): string {
		return session_id();
	}

	public function properties(): array {
		return ['expiration' => (int) ini_get('session.gc_maxlifetime')] + $this->origin->properties();
	}
}
