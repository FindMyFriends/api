<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;

final class FakeDemand implements Demand {
	public function print(Output\Format $format): Output\Format {
		return $format;
	}

	public function retract(): void {
	}

	public function reconsider(array $description): void {
	}
}