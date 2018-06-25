<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Fake
 */
final class FakeForgottenPasswords implements ForgottenPasswords {
	/** @var \FindMyFriends\Domain\Access\Password|null */
	private $password;

	public function __construct(?Password $password = null) {
		$this->password = $password;
	}

	public function remind(string $email): Password {
		return $this->password;
	}
}
