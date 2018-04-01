<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;

/**
 * Soulmates recording request
 */
final class RequestedSoulmates implements Soulmates {
	private $requests;
	private $origin;

	public function __construct(Requests $requests, Soulmates $origin) {
		$this->requests = $requests;
		$this->origin = $origin;
	}

	public function find(int $id): void {
		$this->requests->refresh($id, 'processing');
		try {
			$this->origin->find($id);
			$this->requests->refresh($id, 'succeed');
		} catch (\Throwable $ex) {
			$this->requests->refresh($id, 'failed');
			throw $ex;
		}
	}

	public function matches(Dataset\Selection $selection): \Iterator {
		return $this->origin->matches($selection);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}