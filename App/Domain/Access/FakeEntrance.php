<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

final class FakeEntrance implements Entrance {
	/** @var \FindMyFriends\Domain\Access\Seeker|null */
	private $seeker;

	public function __construct(?Seeker $seeker = null) {
		$this->seeker = $seeker;
	}

	public function enter(array $credentials): Seeker {
		return $this->seeker;
	}

	public function exit(): Seeker {
		return $this->seeker;
	}
}
