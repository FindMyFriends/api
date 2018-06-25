<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Storage;

/**
 * Publisher with limit of refreshes
 */
final class RefreshablePublisher implements Publisher {
	/** @var \FindMyFriends\Domain\Search\Publisher */
	private $origin;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(Publisher $origin, Storage\MetaPDO $database) {
		$this->origin = $origin;
		$this->database = $database;
	}

	/**
	 * @param int $demand
	 * @throws \UnexpectedValueException
	 */
	public function publish(int $demand): void {
		if (!$this->refreshable($demand))
			throw new \UnexpectedValueException('Demand is not refreshable for soulmate yet');
		$this->origin->publish($demand);
	}

	private function refreshable(int $demand): bool {
		return (new Storage\TypedQuery(
			$this->database,
			'SELECT is_soulmate_request_refreshable(?::integer)',
			[$demand]
		))->field();
	}
}
