<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

final class Guest implements Seeker {
	public function id(): string {
		return '0';
	}

	public function properties(): array {
		return ['role' => 'guest'];
	}
}
