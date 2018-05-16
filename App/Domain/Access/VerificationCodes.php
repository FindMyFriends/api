<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

interface VerificationCodes {
	/**
	 * Generate a new unique verification code for the given email
	 * @param string $email
	 * @return \FindMyFriends\Domain\Access\VerificationCode
	 */
	public function generate(string $email): VerificationCode;
}
