<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;

/**
 * Soulmate formatted to be used for public representation
 */
final class PublicSoulmate implements Soulmate {
	/** @var \FindMyFriends\Domain\Search\Soulmate */
	private $origin;

	/** @var mixed[] */
	private $hashids;

	public function __construct(Soulmate $origin, array $hashids) {
		$this->origin = $origin;
		$this->hashids = $hashids;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('id', function (?int $id): ?string {
				return $id === null ? $id : $this->hashids['soulmate']->encode($id);
			})
			->adjusted('demand_id', [$this->hashids['demand'], 'encode'])
			->adjusted('evolution_id', function (?int $id): ?string {
				return $id === null ? $id : $this->hashids['evolution']->encode($id);
			})
			->adjusted('searched_at', static function (string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			})
			->adjusted('related_at', static function (?string $datetime): ?string {
				return $datetime === null ? $datetime : (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}

	public function clarify(bool $correct): void {
		$this->origin->clarify($correct);
	}

	public function expose(): void {
		$this->origin->expose();
	}
}
