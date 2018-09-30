<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\SoulmateRequest;

use FindMyFriends\Schema;
use Klapuch\Storage;

final class Structure {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function get(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'id' => ['type' => ['integer']],
				'self_id' => ['type' => ['integer', 'null']],
				'searched_at' => ['type' => 'string', 'format' => 'date-time'],
				'is_refreshable' => ['type' => ['boolean']],
				'refreshable_in' => ['type' => ['integer'], 'minimum' => 0],
				'status' => [
					'type' => ['string'],
					'enum' => (new Schema\PostgresEnum('job_statuses', $this->connection))->values(),
				],
			],
			'required' => [
				'id',
				'self_id',
				'searched_at',
				'is_refreshable',
				'refreshable_in',
				'status',
			],
			'type' => 'object',
		];
	}
}
