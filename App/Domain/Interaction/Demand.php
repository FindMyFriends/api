<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use Klapuch\Output;

interface Demand {
	/**
	 * Print the demand to specified format
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;

	/**
	 * Take back the demand
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function retract(): void;

	/**
	 * Reconsider the current demand by the new description
	 * @param array $description
	 * @throws \UnexpectedValueException
	 * @return void
	 */
	public function reconsider(array $description): void;
}
