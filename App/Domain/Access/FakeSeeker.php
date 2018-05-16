<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

final class FakeSeeker implements Seeker {
	private $id;
	private $properties;

	public function __construct(?string $id = null, ?array $properties = null) {
		$this->id = $id;
		$this->properties = $properties;
	}

	public function id(): string {
		return $this->id;
	}

	public function properties(): array {
		return $this->properties;
	}
}
