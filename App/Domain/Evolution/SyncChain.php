<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Elasticsearch;
use FindMyFriends;
use Klapuch\Dataset;

/**
 * Chain synced with elasticsearch
 */
final class SyncChain implements Chain {
	/** @var \FindMyFriends\Domain\Evolution\Chain */
	private $origin;

	/** @var \FindMyFriends\Elasticsearch\RelationshipEvolutions */
	private $elasticsearch;

	public function __construct(Chain $origin, Elasticsearch\Client $elasticsearch) {
		$this->origin = $origin;
		$this->elasticsearch = new FindMyFriends\Elasticsearch\RelationshipEvolutions($elasticsearch);
	}

	/**
	 * @param array $progress
	 * @throws \UnexpectedValueException
	 * @return int
	 */
	public function extend(array $progress): int {
		$id = $this->origin->extend($progress);
		$this->elasticsearch->index(
			[
				'body' => $progress,
				'id' => $id,
			]
		);
		return $id;
	}

	public function changes(Dataset\Selection $selection): \Iterator {
		return $this->origin->changes($selection);
	}

	public function count(Dataset\Selection $selection): int {
		return $this->origin->count($selection);
	}
}
