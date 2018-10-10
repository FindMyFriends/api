<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

interface Seeker {
	/**
	 * ID of the seeker
	 * @return string
	 */
	public function id(): string;

	/**
	 * Properties of the seeker such as email, role, etc.
	 * @return array
	 */
	public function properties(): array;
}
