<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\SoulmateRequest;

use FindMyFriends\Schema;

final class Structure {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'id' => ['type' => ['integer']],
				'searched_at' => ['type' => 'string', 'format' => 'date-time'],
				'is_repeatable' => ['type' => ['boolean']],
				'status' => [
					'type' => ['string'],
					'enum' => array_merge(
						[null],
						(new Schema\PostgresEnum('job_statuses', $this->database))->values()
					),
				],
			],
			'required' => [
				'id',
				'searched_at',
				'is_repeatable',
				'status',
			],
			'type' => 'object',
		];
	}
}