<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Elasticsearch;
use FindMyFriends;
use Klapuch\Output;

/**
 * Change synced with elasticsearch
 */
final class SyncChange implements Change {
	/** @var int */
	private $id;

	/** @var \FindMyFriends\Domain\Evolution\Change */
	private $origin;

	/** @var \FindMyFriends\Elasticsearch\RelationshipEvolutions */
	private $elasticsearch;

	public function __construct(int $id, Change $origin, Elasticsearch\Client $elasticsearch) {
		$this->id = $id;
		$this->origin = $origin;
		$this->elasticsearch = new FindMyFriends\Elasticsearch\RelationshipEvolutions($elasticsearch);
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format);
	}

	public function affect(array $changes): void {
		$this->origin->affect($changes);
		$this->elasticsearch->update(
			[
				'id' => $this->id,
				'body' => ['doc' => $changes],
			]
		);
	}

	public function revert(): void {
		$this->origin->revert();
		$this->elasticsearch->delete(['id' => $this->id]);
	}
}
