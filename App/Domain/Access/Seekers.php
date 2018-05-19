<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

interface Seekers {
	/**
	 * Join to the community
	 * @param mixed[] $credentials
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function join(array $credentials): Seeker;
}
