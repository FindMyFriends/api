<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;

interface Evolution {
	/**
	 * Print the evolution change to specified format
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;
}