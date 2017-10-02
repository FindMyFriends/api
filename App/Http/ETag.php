<?php
declare(strict_types = 1);
namespace FindMyFriends\Http;

final class ETag {
	private $entity;

	public function __construct(object $entity) {
		$this->entity = $entity;
	}

	public function __toString(): string {
		return sprintf(
			'"%s"',
			md5(
				(new \ReflectionClass($this->entity))->isAnonymous()
					? spl_object_hash($this->entity)
					: serialize($this->entity)
			)
		);
	}
}