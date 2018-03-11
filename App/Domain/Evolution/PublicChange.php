<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Hashids\HashidsInterface;
use Klapuch\Output;

/**
 * Evolution change formatted to be used for public representation
 */
final class PublicChange implements Change {
	private $origin;
	private $hashids;

	public function __construct(Change $origin, HashidsInterface $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	public function affect(array $changes): void {
		$this->origin->affect($changes);
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('id', [$this->hashids, 'encode'])
			->adjusted('evolved_at', function(string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}

	public function revert(): void {
		$this->origin->revert();
	}
}