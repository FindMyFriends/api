<?php
declare(strict_types = 1);
namespace FindMyFriends\Domain\Evolution;

use Klapuch\Output;

/**
 * Change formatted to be used in output
 */
final class FormattedChange implements Change {
	private $origin;

	public function __construct(Change $origin) {
		$this->origin = $origin;
	}

	public function affect(array $changes): void {
		$this->origin->affect($changes);
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