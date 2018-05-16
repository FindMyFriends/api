<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

interface Entrance {
	public const IDENTIFIER = 'id';
	/**
	 * Let the seeker in
	 * @param array $credentials
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function enter(array $credentials): Seeker;

	/**
	 * Let the seeker out
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function exit(): Seeker;
}
