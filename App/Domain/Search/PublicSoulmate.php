<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;

/**
 * Soulmate formatted to be used for public representation
 */
final class PublicSoulmate implements Soulmate {
	private $origin;
	private $hashids;

	public function __construct(Soulmate $origin, array $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('id', function (?int $id): ?string {
				return $id === null ? $id : $this->hashids['soulmate']['hashid']->encode($id);
			})
			->adjusted('demand_id', [$this->hashids['demand']['hashid'], 'encode'])
			->adjusted('evolution_id', function (?int $id): ?string {
				return $id === null ? $id : $this->hashids['evolution']['hashid']->encode($id);
			})
			->adjusted('searched_at', function (string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			})
			->adjusted('related_at', function (?string $datetime): ?string {
				return $datetime === null ? $datetime : (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}

	public function clarify(array $clarification): void {
		$this->origin->clarify($clarification);
	}
}