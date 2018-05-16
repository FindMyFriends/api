<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Cached seeker
 */
final class CachedSeeker implements Seeker {
	private $origin;
	private $id;
	private $properties;

	public function __construct(Seeker $origin) {
		$this->origin = $origin;
	}

	public function id(): string {
		if ($this->id === null)
			$this->id = $this->origin->id();
		return $this->id;
	}

	public function properties(): array {
		if ($this->properties === null)
			$this->properties = $this->origin->properties();
		return $this->properties;
	}
}
