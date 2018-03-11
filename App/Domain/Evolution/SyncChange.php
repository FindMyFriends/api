<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use Elasticsearch;
use Klapuch\Output;

/**
 * Change synced with elasticsearch
 */
final class SyncChange implements Change {
	private const INDEX = 'relationships',
		TYPE = 'evolutions';
	private $id;
	private $origin;
	private $elasticsearch;

	public function __construct(int $id, Change $origin, Elasticsearch\Client $elasticsearch) {
		$this->id = $id;
		$this->origin = $origin;
		$this->elasticsearch = $elasticsearch;
	}

	public function print(Output\Format $format): Output\Format {
		return $this->origin->print($format);
	}

	public function affect(array $changes): void {
		$this->origin->affect($changes);
		$this->elasticsearch->update(
			[
				'index' => self::INDEX,
				'type' => self::TYPE,
				'id' => $this->id,
				'body' => ['doc' => $changes],
			]
		);
	}

	public function revert(): void {
		$this->origin->revert();
		$this->elasticsearch->delete(
			[
				'index' => self::INDEX,
				'type' => self::TYPE,
				'id' => $this->id,
			]
		);
	}
}