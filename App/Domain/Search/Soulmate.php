<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;

interface Soulmate {
	/**
	 * Print the soulmate to specified format
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;

	/**
	 * Update info about soulmate a bit to be correct
	 * @param mixed[] $clarification
	 * @return void
	 */
	public function clarify(array $clarification): void;
}