<?php
declare(strict_types = 1);

namespace FindMyFriends\Http;

final class FakeRole implements Role {
	private $allowed;

	public function __construct(?bool $allowed = null) {
		$this->allowed = $allowed;
	}

	public function allowed(): bool {
		return $this->allowed;
	}
}