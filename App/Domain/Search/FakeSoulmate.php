<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;

/**
 * Fake
 */
final class FakeSoulmate implements Soulmate {
	public function print(Output\Format $format): Output\Format {
		return $format;
	}

	public function clarify(bool $correct): void {
	}

	public function expose(): void {
	}
}
