<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Soulmate;

final class Structure {
	public function get(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'id' => ['type' => 'string'],
				'demand_id' => ['type' => 'string'],
				'evolution_id' => ['type' => 'string'],
				'seeker_id' => ['type' => 'integer'],
				'position' => ['type' => 'integer'],
				'new' => ['type' => 'boolean'],
			],
			'required' => ['id', 'demand_id', 'evolution_id', 'seeker_id', 'position', 'new'],
			'type' => 'object',
		];
	}
}