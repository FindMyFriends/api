<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Klapuch\Output;

/**
 * Evolution change called sequentially one by one behaving as a single one
 */
final class ChainedChange implements Change {
	/** @var \FindMyFriends\Domain\Evolution\Change[] */
	private $origins;

	public function __construct(Change ...$origins) {
		$this->origins = $origins;
	}

	/**
	 * @param array $changes
	 * @throws \UnexpectedValueException
	 */
	public function affect(array $changes): void {
		foreach ($this->origins as $origin)
			$origin->affect($changes);
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function revert(): void {
		foreach ($this->origins as $origin)
			$origin->revert();
	}

}
