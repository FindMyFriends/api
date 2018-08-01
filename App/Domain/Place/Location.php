<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Klapuch\Output;

interface Location {
	/**
	 * Print the location
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;

	/**
	 * Forget I have been there
	 * @throws \UnexpectedValueException
	 */
	public function forget(): void;
}
