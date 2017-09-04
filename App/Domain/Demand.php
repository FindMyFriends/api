<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;

interface Demand {
	/**
	 * Print the demand to specified format
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;

	/**
	 * Take back the demand
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function retract(): void;
}