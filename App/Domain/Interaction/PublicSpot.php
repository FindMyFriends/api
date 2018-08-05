<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use FindMyFriends\Domain\Place;
use Hashids\HashidsInterface;
use Klapuch\Output;

/**
 * Spot formatted to be used for public representation
 */
final class PublicSpot implements Place\Spot {
	/** @var \FindMyFriends\Domain\Place\Spot */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $demandHashids;

	public function __construct(Place\Spot $origin, HashidsInterface $demandHashids) {
		$this->origin = $origin;
		$this->demandHashids = $demandHashids;
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
			->adjusted('demand_id', [$this->demandHashids, 'encode']);
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function move(array $movement): void {
		$this->origin->move($movement);
	}
}
