<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Token;

final class Structure {
	public function get(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'email' => ['type' => 'string'],
				'password' => ['type' => 'string'],
			],
			'required' => ['email', 'password'],
			'type' => 'object',
		];
	}

	public function post(): array {
		return $this->get();
	}
}
