<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Hashids\HashidsInterface;
use Klapuch\Output;

/**
 * Spot formatted to be used for public representation
 */
final class PublicSpot implements Spot {
	/** @var \FindMyFriends\Domain\Place\Spot */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $spotHashids;

	public function __construct(Spot $origin, HashidsInterface $spotHashids) {
		$this->origin = $origin;
		$this->spotHashids = $spotHashids;
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
			->adjusted('id', [$this->spotHashids, 'encode'])
			->adjusted('assigned_at', static function(string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function move(array $movement): void {
		$this->origin->move($movement);
	}
}
