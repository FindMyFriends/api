<?php
declare(strict_types = 1);
namespace FindMyFriends\Http;

interface Role {
	/**
	 * Has the role allowed access?
	 * @return bool
	 */
	public function allowed(): bool;
}