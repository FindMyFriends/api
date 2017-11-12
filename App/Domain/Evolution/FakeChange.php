<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

use Klapuch\Output;

/**
 * Fake
 */
final class FakeChange implements Change {
	public function affect(array $changes): void {
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}