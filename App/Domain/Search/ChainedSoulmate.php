<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;

/**
 * Soulmate called sequentially one by one behaving as a single one
 */
final class ChainedSoulmate implements Soulmate {
	/** @var \FindMyFriends\Domain\Search\Soulmate[] */
	private $origins;

	public function __construct(Soulmate ...$origins) {
		$this->origins = $origins;
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}

	public function clarify(array $clarification): void {
		foreach ($this->origins as $origin)
			$origin->clarify($clarification);
	}
}
