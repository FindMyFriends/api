<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Activity;

use Klapuch\Output;

interface Notification {
	/**
	 * Received notification in desired format
	 * @param \Klapuch\Output\Format $format
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format;
}
