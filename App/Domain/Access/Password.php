<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;

interface Password {
	/**
	 * Change password to the new one
	 * @param string $password
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function change(string $password): void;

	/**
	 * Print the password
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;
}
