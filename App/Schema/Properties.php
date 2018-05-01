<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema;

interface Properties {
	/**
	 * key-value pairs
	 * @return mixed[]
	 */
	public function objects(): array;
}
