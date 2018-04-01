<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;

/**
 * Request formatted to be used for public representation
 */
final class PublicRequest implements Request {
	private $origin;

	public function __construct(Request $origin) {
		$this->origin = $origin;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format)
			->adjusted('searched_at', function (string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}
}