<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Dataset;

/**
 * Soulmates recording request
 */
final class RequestedSoulmates implements Soulmates {
	/** @var int */
	private $request;

	/** @var \FindMyFriends\Domain\Search\Requests */
	private $requests;

	/** @var \FindMyFriends\Domain\Search\Soulmates */
	private $origin;

	public function __construct(int $request, Requests $requests, Soulmates $origin) {
		$this->request = $request;
		$this->requests = $requests;
		$this->origin = $origin;
	}

	public function seek(): void {
		$this->requests->refresh('processing', $this->request);
		try {
			$this->origin->seek();
			$this->requests->refresh('succeed', $this->request);
		} catch (\Throwable $ex) {
			$this->requests->refresh('failed', $this->request);
			throw $ex;
		}
	}

	/**
	 * @param \Klapuch\Dataset\Selection $selection
	 * @throws \UnexpectedValueException
	 * @return \Iterator
	 */
	public function matches(Dataset\Selection $selection): \Iterator {
		return $this->origin->matches($selection);
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
