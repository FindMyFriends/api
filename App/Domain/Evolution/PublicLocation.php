<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Hashids\HashidsInterface;
use Klapuch\Output;

/**
 * Location formatted to be used for public representation
 */
final class PublicLocation implements Location {
	/** @var \FindMyFriends\Domain\Evolution\Location */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $locationHashids;

	/** @var \Hashids\HashidsInterface */
	private $evolutionHashids;

	public function __construct(
		Location $origin,
		HashidsInterface $locationHashids,
		HashidsInterface $evolutionHashids
	) {
		$this->origin = $origin;
		$this->locationHashids = $locationHashids;
		$this->evolutionHashids = $evolutionHashids;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function forget(): void {
		$this->origin->forget();
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('id', [$this->locationHashids, 'encode'])
			->adjusted('evolution_id', [$this->evolutionHashids, 'encode'])
			->adjusted('assigned_at', function(string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}
}
