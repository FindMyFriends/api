<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

interface ForgottenPasswords {
	/**
	 * Remind forgotten password to the seeker by the given email
	 * @param string $email
	 * @throws \OverflowException
	 * @return \FindMyFriends\Domain\Access\Password
	 */
	public function remind(string $email): Password;
}
