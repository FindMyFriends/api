<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

interface Assertion {
	/**
	 * Assert against predefined value
	 * @return void
	 */
	public function assert(): void;
}