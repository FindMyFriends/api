<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

/**
 * Constant seeker without any roundtrips
 */
final class ConstantSeeker implements Seeker {
	private const SENSITIVE_COLUMNS = ['id', 'password'];
	private $id;
	private $properties;

	public function __construct(string $id, array $properties) {
		$this->id = $id;
		$this->properties = $properties;
	}

	public function id(): string {
		return $this->id;
	}

	public function properties(): array {
		return array_diff_ukey(
			$this->properties,
			array_flip(self::SENSITIVE_COLUMNS),
			'strcasecmp'
		);
	}
}
