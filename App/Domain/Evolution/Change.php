<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Klapuch\Output;

interface Change {
	/**
	 * Print the evolution change to specified format
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;

	/**
	 * Affect the history
	 * @param array $changes
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function affect(array $changes): void;

	/**
	 * Revert the current change
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function revert(): void;
}
