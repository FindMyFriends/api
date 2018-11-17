<?php
declare(strict_types = 1);

namespace FindMyFriends\Configuration;

use Hashids\Hashids;
use Klapuch\Configuration;

/**
 * Each hashid instantiated to Hashids class
 */
final class CreatedHashids implements Configuration\Source {
	/** @var \Klapuch\Configuration\Source */
	private $origin;

	public function __construct(Configuration\Source $origin) {
		$this->origin = $origin;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function read(): array {
		$configuration = $this->origin->read();
		return (array) array_combine(
			array_keys($configuration),
			array_map(
				static function (string $salt, int $length): Hashids {
					return new Hashids($salt, $length);
				},
				array_column($configuration, 'salt'),
				array_column($configuration, 'length')
			)
		);
	}
}
