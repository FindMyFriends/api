<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

interface Job {
	public function fulfill(): void;

	public function name(): string;
}
