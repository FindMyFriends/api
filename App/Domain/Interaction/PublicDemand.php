<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Interaction;

use Hashids\HashidsInterface;
use Klapuch\Output;

/**
 * Demand formatted to be used for public representation
 */
final class PublicDemand implements Demand {
	/** @var \FindMyFriends\Domain\Interaction\Demand */
	private $origin;

	/** @var \Hashids\HashidsInterface */
	private $hashids;

	public function __construct(Demand $origin, HashidsInterface $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('id', [$this->hashids, 'encode'])
			->adjusted('created_at', static function(string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function retract(): void {
		$this->origin->retract();
	}

	/**
	 * @param array $description
	 * @throws \UnexpectedValueException
	 */
	public function reconsider(array $description): void {
		$this->origin->reconsider($description);
	}
}
