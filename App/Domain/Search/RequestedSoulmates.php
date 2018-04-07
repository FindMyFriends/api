<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;

/**
 * Soulmates recording request
 */
final class RequestedSoulmates implements Soulmates {
	private $request;
	private $requests;
	private $origin;

	public function __construct(int $request, Requests $requests, Soulmates $origin) {
		$this->request = $request;
		$this->requests = $requests;
		$this->origin = $origin;
	}

	public function find(): void {
		$this->requests->refresh('processing', $this->request);
		try {
			$this->origin->find();
			$this->requests->refresh('succeed', $this->request);
		} catch (\Throwable $ex) {
			$this->requests->refresh('failed', $this->request);
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