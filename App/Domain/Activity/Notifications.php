<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Activity;

use Klapuch\Dataset;

interface Notifications {
	/**
	 * Received notifications
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return \Iterator
	 */
	public function receive(Dataset\Selection $selection): \Iterator;

	/**
	 * Count all notifications
	 * @param \Klapuch\Dataset\Selection $selection
	 * @return int
	 */
	public function count(Dataset\Selection $selection): int;
}
