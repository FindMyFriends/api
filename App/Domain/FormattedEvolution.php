<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain;

use Klapuch\Output;

/**
 * Evolution formatted to be used in output
 */
final class FormattedEvolution implements Evolution {
	private $origin;

	public function __construct(Evolution $origin) {
		$this->origin = $origin;
	}

	public function change(array $changes): void {
		$this->origin->change($changes);
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('evolved_at', function(string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			})->adjusted('general', function(array $general): array {
				return ['age' => array_map('intval', $general['age'])] + $general;
			});
	}
}