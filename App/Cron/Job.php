<?php
declare(strict_types = 1);

namespace FindMyFriends\Cron;

interface Job {
	public function fulfill(): void;
	public function name(): string;
}
