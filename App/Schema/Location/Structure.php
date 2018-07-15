<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Location;

use FindMyFriends\Schema;

final class Structure {
	/** @var \PDO */
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'additionalProperties' => false,
				'assigned_at' => ['type' => 'string', 'format' => 'date-time'],
				'evolution_id' => ['type' => 'string'],
				'id' => ['type' => 'string'],
				'coordinates' => [
					'additionalProperties' => false,
					'properties' => [
						'latitude' => ['type' => 'number'],
						'longitude' => ['type' => 'number'],
					],
					'required' => ['latitude', 'longitude'],
					'type' => 'object',
				],
				'met_at' => [
					'additionalProperties' => false,
					'properties' => [
						'additionalProperties' => false,
						'moment' => ['type' => 'string', 'format' => 'date-time'],
						'timeline_side' => [
							'type' => 'string',
							'enum' => (new Schema\PostgresEnum('timeline_sides', $this->database))->values(),
						],
						'approximation' => ['type' => ['string', 'null']],
					],
					'required' => ['moment', 'timeline_side', 'approximation'],
					'type' => 'object',
				],
				'required' => ['coordinates', 'met_at', 'assigned_at', 'id', 'evolution_id'],
				'type' => 'object',
			],
		];
	}

	public function post(): array {
		$get = $this->get();
		$properties = &$get['properties'];
		unset($properties['id']);
		unset($properties['evolution_id']);
		unset($properties['assigned_at']);
		unset($properties['required'][array_search('id', $properties['required'], true)]);
		unset($properties['required'][array_search('evolution_id', $properties['required'], true)]);
		unset($properties['required'][array_search('assigned_at', $properties['required'], true)]);
		return $get;
	}
}
