<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

final class FakeSeeker implements Seeker {
	/** @var null|string */
	private $id;

	/** @var mixed[]|null */
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
