<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Description;

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
			'definitions' => [
				'rating' => [
					'type' => ['integer', 'null'],
					'minimum' => 0,
					'maximum' => 10,
				],
				'eye' => [
					'additionalProperties' => false,
					'properties' => [
						'color_id' => ['type' => ['integer', 'null']],
						'lenses' => ['type' => ['boolean', 'null']],
					],
					'required' => ['lenses', 'color_id'],
					'type' => 'object',
				],
				'age' => [
					'type' => ['integer'],
					'minimum' => 15,
					'maximum' => 130,
				],
			],
			'additionalProperties' => false,
			'properties' => [
				'body' => [
					'additionalProperties' => false,
					'properties' => [
						'build_id' => ['type' => ['integer', 'null']],
						'breast_size' => [
							'type' => ['string', 'null'],
							'enum' => array_merge([null], (new Schema\PostgresEnum('breast_sizes_enum', $this->database))->values()),
						],
					],
					'required' => ['build_id'],
					'type' => 'object',
				],
				'hair' => [
					'additionalProperties' => false,
					'properties' => [
						'color_id' => ['type' => ['integer', 'null']],
						'highlights' => ['type' => ['boolean', 'null']],
						'length_id' => ['type' => ['integer', 'null']],
						'nature' => ['type' => ['boolean', 'null']],
						'roots' => ['type' => ['boolean', 'null']],
						'style_id' => ['type' => ['integer', 'null']],
					],
					'required' => [
						'roots',
						'length_id',
						'highlights',
						'color_id',
						'nature',
						'style_id',
					],
					'type' => 'object',
				],
				'beard' => [
					'additionalProperties' => false,
					'properties' => [
						'color_id' => ['type' => ['integer', 'null']],
						'length_id' => ['type' => ['integer', 'null']],
						'style_id' => ['type' => ['integer', 'null']],
					],
					'type' => 'object',
					'required' => ['color_id', 'length_id'],
				],
				'eye' => [
					'additionalProperties' => false,
					'properties' => [
						'left' => ['$ref' => '#/definitions/eye'],
						'right' => ['$ref' => '#/definitions/eye'],
					],
				],
				'eyebrow' => [
					'additionalProperties' => false,
					'properties' => [
						'color_id' => ['type' => ['integer', 'null']],
						'care' => ['$ref' => '#/definitions/rating'],
					],
					'required' => ['color_id', 'care'],
					'type' => 'object',
				],
				'teeth' => [
					'additionalProperties' => false,
					'properties' => [
						'braces' => ['type' => ['boolean', 'null']],
						'care' => ['$ref' => '#/definitions/rating'],
					],
					'required' => ['care', 'braces'],
					'type' => 'object',
				],
				'face' => [
					'additionalProperties' => false,
					'properties' => [
						'care' => ['$ref' => '#/definitions/rating'],
						'freckles' => ['type' => ['boolean', 'null']],
						'shape_id' => ['type' => ['integer', 'null']],
					],
					'required' => ['shape_id', 'care', 'freckles'],
					'type' => 'object',
				],
				'general' => [
					'additionalProperties' => false,
					'properties' => [
						'age' => [
							'additionalProperties' => false,
							'properties' => [
								'from' => ['$ref' => '#/definitions/age'],
								'to' => ['$ref' => '#/definitions/age'],
							],
							'required' => ['from', 'to'],
							'type' => 'object',
						],
						'firstname' => ['type' => ['string', 'null']],
						'sex' => [
							'type' => 'string',
							'enum' => (new Schema\PostgresEnum('sex_enum', $this->database))->values(),
						],
						'lastname' => ['type' => ['string', 'null']],
						'ethnic_group_id' => ['type' => 'integer'],
					],
					'required' => [
						'lastname',
						'firstname',
						'ethnic_group_id',
						'age',
						'sex',
					],
					'type' => 'object',
				],
				'hands' => [
					'additionalProperties' => false,
					'properties' => [
						'nails' => [
							'additionalProperties' => false,
							'properties' => [
								'color_id' => ['type' => ['integer', 'null']],
								'length_id' => ['type' => ['integer', 'null']],
							],
							'required' => ['color_id', 'length_id'],
							'type' => 'object',
						],
						'care' => ['$ref' => '#/definitions/rating'],
						'visible_veins' => ['type' => ['boolean', 'null']],
					],
					'required' => [
						'nails',
						'care',
						'visible_veins',
					],
					'type' => 'object',
				],
			],
			'required' => [
				'general',
				'face',
				'hair',
				'body',
				'hands',
			],
			'type' => 'object',
		];
	}

	public function put(): array {
		return $this->get();
	}

	public function post(): array {
		return $this->put();
	}
}
