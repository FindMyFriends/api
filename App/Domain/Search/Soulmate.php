<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;

interface Soulmate {
	/**
	 * Print the soulmate match to specified format
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;

	/**
	 * Update info about soulmate a bit to be correct
	 * @param bool $correct
	 * @return void
	 */
	public function clarify(bool $correct): void;

	/**
	 * Allow seeker to view information about found person
	 * @return void
	 */
	public function expose(): void;
}
