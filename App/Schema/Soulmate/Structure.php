<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Soulmate;

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
				'id' => ['type' => ['string', 'null']],
				'demand_id' => ['type' => 'string'],
				'evolution_id' => ['type' => ['string', 'null']],
				'seeker_id' => ['type' => ['integer', 'null']],
				'position' => ['type' => ['integer', 'null']],
				'new' => ['type' => ['boolean', 'null']],
				'searched_at' => ['type' => 'string', 'format' => 'date-time'],
				'related_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
				'is_correct' => ['type' => ['boolean', 'null']],
				'is_exposed' => ['type' => ['boolean', 'null']],
				'ownership' => [
					'type' => ['string'],
					'enum' => (new Schema\PostgresConstant('ownerships', $this->connection))->values(),
				],
			],
			'required' => [
				'id',
				'demand_id',
				'evolution_id',
				'seeker_id',
				'position',
				'new',
				'searched_at',
				'related_at',
				'is_correct',
				'is_exposed',
				'ownership',
			],
			'type' => 'object',
		];
	}

	public function patch(): array {
		$schema = $this->get();
		$properties = &$schema['properties'];
		unset(
			$properties['id'],
			$properties['demand_id'],
			$properties['evolution_id'],
			$properties['seeker_id'],
			$properties['position'],
			$properties['new'],
			$properties['searched_at'],
			$properties['related_at'],
			$properties['ownerships']
		);
		$schema['anyOf'] = [
			['required' => ['is_correct']],
			['required' => ['is_exposed']],
		];
		$properties['is_correct']['type'] = ['boolean'];
		$properties['is_exposed']['type'] = ['boolean'];
		unset($schema['required']);
		return $schema;
	}
}
