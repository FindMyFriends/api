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
			});
	}

	public function retract(): void {
		$this->origin->retract();
	}
}