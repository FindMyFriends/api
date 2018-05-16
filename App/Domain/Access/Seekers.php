<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

interface Seekers {
	/**
	 * Register a new seeker by the given email, password and role
	 * @param string $email
	 * @param string $password
	 * @param string $role
	 * @throw \InvalidArgumentException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function register(
		string $email,
		string $password,
		string $role
	): Seeker;
}
