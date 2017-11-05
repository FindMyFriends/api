<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;

/**
 * Demand formatted to be used in output
 */
final class FormattedDemand implements Demand {
	private $origin;

	public function __construct(Demand $origin) {
		$this->origin = $origin;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('created_at', function(string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			})->adjusted('location', function(array $location): array {
				[$begin, $end] = explode(',', str_replace('"', '', trim($location['met_at'], '[()]')), 2);
				return [
					'met_at' => sprintf(
						'%s"%s","%s"%s',
						substr($location['met_at'], 0, 1),
						(new \DateTime($begin))->format(\DateTime::ATOM),
						(new \DateTime($end))->format(\DateTime::ATOM),
						substr($location['met_at'], -1, 1)
					),
					] + $location;
			})->adjusted('general', function(array $general): array {
				return ['birth_year' => array_map('intval', $general['birth_year'])] + $general;
			});
	}

	public function retract(): void {
		$this->origin->retract();
	}

	public function reconsider(array $description): void {
		$this->origin->reconsider($description);
	}
}