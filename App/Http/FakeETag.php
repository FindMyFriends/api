<?php
declare(strict_types = 1);

namespace FindMyFriends\Http;

final class FakeETag implements ETag {
	private $exists;
	private $get;

	public function __construct(?bool $exists = null, ?string $get = null) {
		$this->exists = $exists;
		$this->get = $get;
	}

	public function exists(): bool {
		return $this->exists;
	}

	public function get(): string {
		return $this->get;
	}

	public function set(object $entity): ETag {
		return new self();
	}
}