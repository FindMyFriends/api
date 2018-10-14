<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Activity;

use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Notifications formatted to be used for public representation
 */
final class PublicNotifications implements Notifications {
	/** @var \FindMyFriends\Domain\Activity\Notifications */
	private $origin;

	public function __construct(Notifications $origin) {
		$this->origin = $origin;
	}

	public function receive(Dataset\Selection $selection): \Iterator {
		return new Iterator\Mapped(
			$this->origin->receive($selection),
			static function(Notification $notification): Notification {
				return new PublicNotification($notification);
			}
		);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}
