<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;
use Klapuch\Iterator;

/**
 * Requests formatted to be used for public representation
 */
final class PublicRequests implements Requests {
	/** @var \FindMyFriends\Domain\Search\Requests */
	private $origin;

	public function __construct(Requests $origin) {
		$this->origin = $origin;
	}

	public function refresh(string $status, ?int $self = null): int {
		return $this->origin->refresh($status, $self);
	}

	/**
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return \Iterator
	 */
	public function all(Dataset\Selection $selection): \Iterator {
		return new Iterator\Mapped(
			$this->origin->all($selection),
			static function(Request $request): Request {
				return new PublicRequest($request);
			}
		);
	}

	/**
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return int
	 */
	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}
