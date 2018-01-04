<?php
declare(strict_types = 1);

namespace FindMyFriends\Http;

interface ETag {
	/**
	 * Does the tag exists?
	 * @return bool
	 */
	public function exists(): bool;

	/**
	 * Get the tag
	 * @return string
	 */
	public function get(): string;

	/**
	 * Set a new tag for the entity
	 * @param object $entity
	 * @return \FindMyFriends\Http\ETag
	 */
	public function set(object $entity): self;
}