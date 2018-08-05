<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Place;

use Klapuch\Output;

/**
 * Spot called sequentially one by one behaving as a single one
 */
final class ChainedSpot implements Spot {
	/** @var \FindMyFriends\Domain\Place\Spot[] */
	private $spots;

	public function __construct(Spot ...$spots) {
		$this->spots = $spots;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function forget(): void {
		foreach ($this->spots as $spot) {
			$spot->forget();
		}
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function move(array $movement): void {
		foreach ($this->spots as $spot) {
			$spot->move($movement);
		}
	}
}
