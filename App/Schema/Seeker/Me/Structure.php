<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Seeker\Me;

final class Structure {
	public function get(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => ['email' => ['type' => 'string']] + [
				'contact' => [
					'type' => 'object',
					'additionalProperties' => false,
					'properties' => [
						'facebook' => ['type' => ['string', 'null']],
						'instagram' => ['type' => ['string', 'null']],
						'phone_number' => ['type' => ['string', 'null']],
					],
					'required' => ['facebook', 'instagram', 'phone_number'],
				],
			],
			'required' => ['email', 'contact'],
			'type' => 'object',
		];
	}
}
