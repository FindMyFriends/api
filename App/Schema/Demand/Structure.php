<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Demand;

use FindMyFriends\Schema;

final class Structure {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		$description = (new Schema\Description\Structure($this->database))->get();
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'created_at' => ['type' => 'string'],
				'seeker_id' => ['type' => 'integer'],
				'id' => ['type' => 'string'],
				'location' => [
					'additionalProperties' => false,
					'properties' => [
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
								'moment' => ['type' => ['string'], 'format' => 'date-time'],
								'timeline_side' => [
									'type' => ['string'],
									'enum' => (new Schema\PostgresEnum('timeline_sides', $this->database))->values(),
								],
								'approximation' => ['type' => ['string', 'null']],
							],
							'required' => ['moment', 'timeline_side', 'approximation'],
							'type' => 'object',
						],
					],
					'required' => ['coordinates', 'met_at'],
					'type' => 'object',
				],
			] + $description['properties'],
			'required' => array_merge(['location'], $description['required']),
			'type' => 'object',
		] + $description;
	}

	public function put(): array {
		$schema = $this->get();
		$description = (new Schema\Description\Structure($this->database))->put();
		$schema['properties'] = $description['properties'] + $schema['properties'];
		$schema['definitions'] = $description['definitions'] + $schema['definitions'];
		$properties = &$schema['properties'];
		unset($properties['created_at']);
		unset($properties['seeker_id']);
		unset($properties['id']);
		return $schema;
	}

	public function post(): array {
		return $this->put();
	}
}