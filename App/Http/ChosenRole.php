<?php
declare(strict_types = 1);

namespace FindMyFriends\Http;

use FindMyFriends\Domain\Access;

/**
 * Chosen role from the listed ones
 */
final class ChosenRole implements Role {
	private $seeker;
	private $roles;

	public function __construct(Access\Seeker $seeker, array $roles) {
		$this->seeker = $seeker;
		$this->roles = $roles;
	}

	public function allowed(): bool {
		return (bool) array_uintersect(
			[$this->seeker->properties()['role'] ?? 'guest'],
			$this->roles,
			'strcasecmp'
		);
	}
}
