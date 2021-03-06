<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Klapuch\Output;

interface Spot {
	/**
	 * Print the spot
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

	/**
	 * Move from one spot to another
	 * @throws \UnexpectedValueException
	 */
	public function move(array $movement): void;
}
