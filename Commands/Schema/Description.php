<?php
declare(strict_types = 1);

namespace FindMyFriends\Commands\Schema;

final class Description {
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
						'color_id' => [
							'type' => ['integer', 'null'],
							'enum' => array_merge([null], (new Colors('id', 'eye_colors', $this->database))->values()),
						],
						'lenses' => ['type' => ['boolean', 'null']],
					],
					'required' => ['lenses', 'color_id'],
					'type' => 'object',
				],
				'length_unit' => [
					'type' => ['null', 'string'],
					'enum' => array_merge([null], (new PostgresEnum('length_units', $this->database))->values()),
				],
				'mass_unit' => [
					'type' => ['null', 'string'],
					'enum' => array_merge([null], (new PostgresEnum('mass_units', $this->database))->values()),
				],
				'age' => [
					'type' => ['integer', 'null'],
					'minimum' => 15,
					'maximum' => 130,
				],
			],
			'additionalProperties' => false,
			'properties' => [
				'body' => [
					'additionalProperties' => false,
					'properties' => [
						'build_id' => [
							'type' => ['integer', 'null'],
							'enum' => array_merge([null], (new PostgresTableEnum('id', 'body_builds', $this->database))->values()),
						],
						'weight' => [
							'additionalProperties' => false,
							'properties' => [
								'value' => ['type' => ['number', 'null']],
								'unit' => ['$ref' => '#/definitions/mass_unit'],
							],
							'type' => 'object',
							'required' => ['value', 'unit'],
						],
						'height' => [
							'additionalProperties' => false,
							'properties' => [
								'value' => ['type' => ['number', 'null']],
								'unit' => ['$ref' => '#/definitions/length_unit'],
							],
							'type' => 'object',
							'required' => ['value', 'unit'],
						],
						'breast_size' => [
							'type' => ['string', 'null'],
							'enum' => array_merge([null], (new PostgresEnum('breast_sizes', $this->database))->values()),
						],
					],
					'required' => [
						'build_id',
						'weight',
						'height',
					],
					'type' => 'object',
				],
				'hair' => [
					'additionalProperties' => false,
					'properties' => [
						'color_id' => [
							'type' => ['integer', 'null'],
							'enum' => array_merge([null], (new Colors('id', 'hair_colors', $this->database))->values()),
						],
						'highlights' => ['type' => ['boolean', 'null']],
						'length' => [
							'additionalProperties' => false,
							'properties' => [
								'value' => ['type' => ['number', 'null']],
								'unit' => ['$ref' => '#/definitions/length_unit'],
							],
							'type' => 'object',
							'required' => ['value', 'unit'],
						],
						'nature' => ['type' => ['boolean', 'null']],
						'roots' => ['type' => ['boolean', 'null']],
						'style_id' => [
							'type' => ['integer', 'null'],
							'enum' => array_merge([null], (new PostgresTableEnum('id', 'hair_styles', $this->database))->values()),
						],
					],
					'required' => [
						'roots',
						'length',
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
						'color_id' => [
							'type' => ['integer', 'null'],
							'enum' => array_merge([null], (new Colors('id', 'beard_colors', $this->database))->values()),
						],
						'length' => [
							'additionalProperties' => false,
							'properties' => [
								'value' => ['type' => ['number', 'null']],
								'unit' => [
									'type' => ['null', 'string'],
									'enum' => array_merge([null], (new PostgresEnum('length_units', $this->database))->values()),
								],
							],
							'type' => 'object',
							'required' => ['value', 'unit'],
						],
						'style' => [
							'additionalProperties' => false,
							'type' => ['string', 'null'],
						],
					],
					'type' => 'object',
					'required' => ['color_id', 'length', 'style'],
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
						'color_id' => [
							'type' => ['integer', 'null'],
							'enum' => array_merge([null], (new Colors('id', 'eyebrow_colors', $this->database))->values()),
						],
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
						'shape_id' => [
							'type' => ['integer', 'null'],
							'enum' => array_merge([null], (new PostgresTableEnum('id', 'face_shapes', $this->database))->values()),
						],
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
						'gender' => [
							'type' => 'string',
							'enum' => (new PostgresEnum('genders', $this->database))->values(),
						],
						'lastname' => ['type' => ['string', 'null']],
						'ethnic_group_id' => [
							'type' => 'integer',
							'enum' => (new PostgresTableEnum('id', 'ethnic_groups', $this->database))->values(),
						],
					],
					'required' => [
						'lastname',
						'firstname',
						'ethnic_group_id',
						'age',
						'gender',
					],
					'type' => 'object',
				],
				'hands' => [
					'additionalProperties' => false,
					'properties' => [
						'nails' => [
							'additionalProperties' => false,
							'properties' => [
								'color_id' => [
									'type' => ['integer', 'null'],
									'enum' => array_merge([null], (new Colors('id', 'nail_colors', $this->database))->values()),
								],
								'length' => [
									'additionalProperties' => false,
									'properties' => [
										'value' => ['type' => ['number', 'null']],
										'unit' => ['$ref' => '#/definitions/length_unit'],
									],
									'type' => 'object',
									'required' => ['value', 'unit'],
								],
								'care' => ['$ref' => '#/definitions/rating'],
							],
							'required' => ['color_id', 'length', 'care'],
							'type' => 'object',
						],
						'care' => ['$ref' => '#/definitions/rating'],
						'vein_visibility' => ['$ref' => '#/definitions/rating'],
						'joint_visibility' => ['$ref' => '#/definitions/rating'],
						'hair' => [
							'additionalProperties' => false,
							'properties' => [
								'color_id' => [
									'type' => ['integer', 'null'],
									'enum' => array_merge([null], (new Colors('id', 'hand_hair_colors', $this->database))->values()),
								],
								'amount' => ['type' => ['integer', 'null']],
							],
							'required' => ['color_id', 'amount'],
							'type' => 'object',
						],
					],
					'required' => [
						'nails',
						'care',
						'vein_visibility',
						'joint_visibility',
						'hair',
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