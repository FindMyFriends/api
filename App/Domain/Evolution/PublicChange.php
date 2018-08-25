<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Hashids\HashidsInterface;
use Klapuch\Output;

/**
 * Evolution change formatted to be used for public representation
 */
final class PublicChange implements Change {
	/** @var \FindMyFriends\Domain\Evolution\Change */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $hashids;

	public function __construct(Change $origin, HashidsInterface $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	/**
	 * @param array $changes
	 * @throws \UnexpectedValueException
	 */
	public function affect(array $changes): void {
		$this->origin->affect($changes);
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('id', [$this->hashids, 'encode'])
			->adjusted('evolved_at', static function(string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function revert(): void {
		$this->origin->revert();
	}
}
