<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;

/**
 * Fake
 */
final class FakeEvolution implements Evolution {
	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}