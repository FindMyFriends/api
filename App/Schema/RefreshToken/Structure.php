<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\RefreshToken;

final class Structure {
	public function post(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'token' => ['type' => 'string'],
			],
			'required' => ['token'],
			'type' => 'object',
		];
	}
}
