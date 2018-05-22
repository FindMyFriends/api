<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

interface Publisher {
	/**
	 * Publish the verification message
	 * @throws \UnexpectedValueException
	 * @param string $email
	 */
	public function publish(string $email): void;
}
