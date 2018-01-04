<?php
declare(strict_types = 1);

namespace FindMyFriends\Http;

use Klapuch\Access;

/**
 * Chosen role from the listed ones
 */
final class ChosenRole implements Role {
	private $user;
	private $roles;

	public function __construct(Access\User $user, array $roles) {
		$this->user = $user;
		$this->roles = $roles;
	}

	public function allowed(): bool {
		return (bool) array_uintersect(
			[$this->user->properties()['role'] ?? 'guest'],
			$this->roles,
			'strcasecmp'
		);
	}
}