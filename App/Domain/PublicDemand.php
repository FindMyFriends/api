<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain;

use Hashids\HashidsInterface;
use Klapuch\Output;

/**
 * Demand formatted to be used for public representation
 */
final class PublicDemand implements Demand {
	private $origin;
	private $demandHashid;
	private $soulmateHashid;

	public function __construct(Demand $origin, HashidsInterface $demandHashid, HashidsInterface $soulmateHashid) {
		$this->origin = $origin;
		$this->demandHashid = $demandHashid;
		$this->soulmateHashid = $soulmateHashid;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('id', [$this->demandHashid, 'encode'])
			->adjusted('soulmates', function(array $soulmates): array {
				return array_map([$this->soulmateHashid, 'encode'], $soulmates);
			})
			->adjusted('created_at', function(string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			})->adjusted('location', function(array $location): array {
				return array_replace_recursive(
					$location,
					[
						'met_at' => [
							'moment' => (new \DateTime($location['met_at']['moment']))->format(\DateTime::ATOM),
						],
					]
				);
			});
	}

	public function retract(): void {
		$this->origin->retract();
	}

	public function reconsider(array $description): void {
		$this->origin->reconsider($description);
	}
}
