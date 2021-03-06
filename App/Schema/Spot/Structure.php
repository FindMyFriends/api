<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Spot;

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
							'enum' => (new Schema\PostgresConstant('timeline_sides', $this->connection))->values(),
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

	public function put(): array {
		return $this->post();
	}

	public function patch(): array {
		$put = $this->put();
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => $put['properties'],
			'anyOf' => array_map(static function (string $field): array {
				return ['required' => [$field]];
			}, $put['required']),
			'type' => 'object',
		];
	}
}
