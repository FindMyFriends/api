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
				'assigned_at' => ['type' => 'string', 'format' => 'date-time'],
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
			],
			'required' => ['coordinates', 'met_at', 'assigned_at', 'id'],
			'type' => 'object',
		];
	}

	public function post(): array {
		$get = $this->get();
		$properties = &$get['properties'];
		unset($properties['id']);
		unset($properties['assigned_at']);
		unset($get['required'][array_search('id', $get['required'], true)]);
		unset($get['required'][array_search('assigned_at', $get['required'], true)]);
		return $get;
	}
}
