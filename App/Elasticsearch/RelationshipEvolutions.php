<?php
declare(strict_types = 1);

namespace FindMyFriends\Elasticsearch;

use Elasticsearch;

final class RelationshipEvolutions {
	private const INDEX = 'relationships',
		TYPE = 'evolutions';
	private $elasticsearch;

	public function __construct(Elasticsearch\Client $elasticsearch) {
		$this->elasticsearch = $elasticsearch;
	}

	public function update(array $params): array {
		return $this->elasticsearch->update(
			['index' => self::INDEX, 'type' => self::TYPE] + $params
		);
	}

	public function delete(array $params): array {
		return $this->elasticsearch->delete(
			['index' => self::INDEX, 'type' => self::TYPE] + $params
		);
	}

	public function index(array $params): array {
		return $this->elasticsearch->index(
			['index' => self::INDEX, 'type' => self::TYPE] + $params
		);
	}

	public function search(array $params): array {
		return $this->elasticsearch->search(
			['index' => self::INDEX, 'type' => self::TYPE] + $params
		);
	}
}
