<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Activation;

final class Structure {
	public function post(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'code' => ['type' => 'string'],
			],
			'required' => ['code'],
			'type' => 'object',
		];
	}
}
