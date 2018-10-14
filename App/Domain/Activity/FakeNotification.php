<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Activity;

use Klapuch\Output;

final class FakeNotification implements Notification {
	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}
