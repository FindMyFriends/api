<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Klapuch\Output;

/**
 * Demand called sequentially one by one behaving as a single one
 */
final class ChainedDemand implements Demand {
	/** @var \FindMyFriends\Domain\Demand[] */
	private $origins;

	public function __construct(Demand ...$origins) {
		$this->origins = $origins;
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function retract(): void {
		foreach ($this->origins as $origin)
			$origin->retract();
	}

	/**
	 * @param array $description
	 * @throws \UnexpectedValueException
	 */
	public function reconsider(array $description): void {
		foreach ($this->origins as $origin)
			$origin->reconsider($description);
	}
}
