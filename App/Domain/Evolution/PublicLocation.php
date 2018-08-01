<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Place;
use Hashids\HashidsInterface;
use Klapuch\Output;

/**
 * Location formatted to be used for public representation
 */
final class PublicLocation implements Place\Location {
	/** @var \FindMyFriends\Domain\Place\Location */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $evolutionHashids;

	public function __construct(Place\Location $origin, HashidsInterface $evolutionHashids) {
		$this->origin = $origin;
		$this->evolutionHashids = $evolutionHashids;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function forget(): void {
		$this->origin->forget();
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('evolution_id', [$this->evolutionHashids, 'encode']);
	}
}
