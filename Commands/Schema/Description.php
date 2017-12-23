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
					'properties' =>
						[
							'id' => ['type' => 'integer'],
							'color' => [
								'additionalProperties' => false,
								'properties' => [
									'id' => [
										'type' => ['integer', 'null'],
										'enum' => array_merge([null], (new Colors('id', 'eye_colors', $this->database))->values()),
									],
									'name' => [
										'type' => ['string', 'null'],
										'enum' => array_merge([null], (new Colors('name', 'eye_colors', $this->database))->values()),
									],
									'hex' => [
										'type' => ['string', 'null'],
										'enum' => array_merge([null], (new Colors('hex', 'eye_colors', $this->database))->values()),
									],
								],
								'required' => ['id', 'name', 'hex'],
								'type' => 'object',
							],
							'lenses' => ['type' => ['boolean', 'null']],
						],
					'required' => ['lenses', 'color'],
					'type' => 'object',
				],
			],
			'additionalProperties' => false,
			'properties' =>
				[
					'body' => [
						'additionalProperties' => false,
						'properties' =>
								[
									'build' =>
										[
											'additionalProperties' => false,
											'properties' => [
												'id' => [
													'type' => ['integer', 'null'],
													'enum' => array_merge(
														[null],
														(new PostgresTableEnum('id', 'body_builds', $this->database))->values()
													),
												],
												'value' => [
													'type' => ['string', 'null'],
													'enum' => array_merge(
														[null],
														(new PostgresTableEnum('value', 'body_builds', $this->database))->values()
													),
												],
											],
											'required' => [
												'id',
												'value',
											],
											'type' => 'object',
										],
									'weight' => ['type' => ['integer', 'null']],
									'height' => ['type' => ['integer', 'null']],
									'skin_color' => [
										'additionalProperties' => false,
										'properties' => [
											'id' => [
												'type' => ['integer', 'null'],
												'enum' => array_merge([null], (new Colors('id', 'skin_colors', $this->database))->values()),
											],
											'name' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('name', 'skin_colors', $this->database))->values()),
											],
											'hex' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('hex', 'skin_colors', $this->database))->values()),
											],
										],
										'required' => [
											'id',
											'name',
											'hex',
										],
										'type' => 'object',
									],
								],
						'required' =>
								[
									'build',
									'skin_color',
									'weight',
									'height',
								],
						'type' => 'object',
					],
					'hair' => [
						'additionalProperties' => false,
						'properties' =>
								[
									'color' => [
										'additionalProperties' => false,
										'properties' => [
											'id' => [
												'type' => ['integer', 'null'],
												'enum' => array_merge([null], (new Colors('id', 'hair_colors', $this->database))->values()),
											],
											'name' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('name', 'hair_colors', $this->database))->values()),
											],
											'hex' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('hex', 'hair_colors', $this->database))->values()),
											],
										],
										'required' => [
											'id',
											'name',
											'hex',
										],
										'type' => 'object',
									],
									'highlights' => ['type' => ['boolean', 'null']],
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
										'required' => [
											'value',
											'unit',
										],
									],
									'nature' => ['type' => ['boolean', 'null']],
									'roots' => ['type' => ['boolean', 'null']],
									'style' => ['type' => ['string', 'null']],
								],
						'required' =>
								[
									'roots',
									'length',
									'highlights',
									'color',
									'nature',
									'style',
								],
						'type' => 'object',
					],
					'face' => [
						'additionalProperties' => false,
						'properties' =>
								[
									'beard' =>
										[
											'additionalProperties' => false,
											'properties' => [
												'id' => ['type' => 'integer'],
												'color' => [
													'additionalProperties' => false,
													'properties' => [
														'id' => [
															'type' => ['integer', 'null'],
															'enum' => array_merge([null], (new Colors('id', 'beard_colors', $this->database))->values()),
														],
														'name' => [
															'type' => ['string', 'null'],
															'enum' => array_merge([null], (new Colors('name', 'beard_colors', $this->database))->values()),
														],
														'hex' => [
															'type' => ['string', 'null'],
															'enum' => array_merge([null], (new Colors('hex', 'beard_colors', $this->database))->values()),
														],
													],
													'required' => [
														'id',
														'name',
														'hex',
													],
													'type' => 'object',
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
													'required' => [
														'value',
														'unit',
													],
												],
												'style' => [
													'additionalProperties' => false,
													'type' => ['string', 'null'],
												],
											],
											'type' => 'object',
											'required' => [
												'id',
												'color',
												'length',
												'style',
											],
										],
									'care' => ['$ref' => '#/definitions/rating'],
									'eyebrow' => [
										'additionalProperties' => false,
										'properties' => [
											'id' => [
												'type' => 'integer',
											],
											'color' => [
												'additionalProperties' => false,
												'properties' => [
													'id' => [
														'type' => ['integer', 'null'],
														'enum' => array_merge([null], (new Colors('id', 'eyebrow_colors', $this->database))->values()),
													],
													'name' => [
														'type' => ['string', 'null'],
														'enum' => array_merge([null], (new Colors('name', 'eyebrow_colors', $this->database))->values()),
													],
													'hex' => [
														'type' => ['string', 'null'],
														'enum' => array_merge([null], (new Colors('hex', 'eyebrow_colors', $this->database))->values()),
													],
												],
												'required' => [
													'id',
													'name',
													'hex',
												],
												'type' => 'object',
											],
											'care' => ['$ref' => '#/definitions/rating'],
										],
										'required' =>
											[
												'color',
												'care',
											],
										'type' => 'object',
									],
									'freckles' => ['type' => ['boolean', 'null']],
									'eye' =>
										[
											'additionalProperties' => false,
											'properties' =>
												[
													'left' => ['$ref' => '#/definitions/eye'],
													'right' => ['$ref' => '#/definitions/eye'],
												],
										],
									'shape' => [
										'type' => ['string', 'null'],
										'enum' => array_merge([null], (new PostgresEnum('face_shapes', $this->database))->values()),
									],
									'teeth' =>
										[
											'additionalProperties' => false,
											'properties' =>
												[
													'id' => ['type' => 'integer'],
													'braces' => ['type' => ['boolean', 'null']],
													'care' => ['$ref' => '#/definitions/rating'],
												],
											'required' =>
												[
													'care',
													'braces',
												],
											'type' => 'object',
										],
								],
						'required' =>
								[
									'beard',
									'teeth',
									'shape',
									'care',
									'eye',
									'eyebrow',
									'freckles',
								],
						'type' => 'object',
					],
					'general' => [
						'additionalProperties' => false,
						'properties' =>
								[
									'age' =>
										[
											'additionalProperties' => false,
											'properties' =>
												[
													'from' =>
														[
															'type' => ['integer', 'null'],
															'minimum' => 15,
															'maximum' => 130,
														],
													'to' =>
														[
															'type' => ['integer', 'null'],
															'minimum' => 15,
															'maximum' => 130,
														],
												],
											'required' => ['from', 'to'],
											'type' => 'object',
										],
									'firstname' => ['type' => ['string', 'null']],
									'gender' =>
										[
											'type' => 'string',
											'enum' => (new PostgresEnum('genders', $this->database))->values(),
										],
									'lastname' => ['type' => ['string', 'null']],
									'race' =>
										[
											'type' => 'object',
											'required' => [
												'id',
												'value',
											],
											'additionalProperties' => false,
											'properties' => [
												'id' => [
													'type' => 'integer',
													'enum' => (new PostgresTableEnum('id', 'races', $this->database))->values(),
												],
												'value' => [
													'type' => 'string',
													'enum' => (new PostgresTableEnum('value', 'races', $this->database))->values(),
												],
											],
										],
								],
						'required' =>
								[
									'lastname',
									'firstname',
									'race',
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
									'color' => [
										'additionalProperties' => false,
										'properties' => [
											'id' => [
												'type' => ['integer', 'null'],
												'enum' => array_merge([null], (new Colors('id', 'nail_colors', $this->database))->values()),
											],
											'name' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('name', 'nail_colors', $this->database))->values()),
											],
											'hex' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('hex', 'nail_colors', $this->database))->values()),
											],
										],
										'required' => [
											'id',
											'name',
											'hex',
										],
										'type' => 'object',
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
										'required' => [
											'value',
											'unit',
										],
									],
									'care' => ['$ref' => '#/definitions/rating'],
								],
								'required' =>
									[
										'color',
										'length',
										'care',
									],
								'type' => 'object',
							],
							'care' => ['$ref' => '#/definitions/rating'],
							'vein_visibility' => ['$ref' => '#/definitions/rating'],
							'joint_visibility' => ['$ref' => '#/definitions/rating'],
							'hair' => [
								'additionalProperties' => false,
								'properties' => [
									'color' => [
										'additionalProperties' => false,
										'properties' => [
											'id' => [
												'type' => ['integer', 'null'],
												'enum' => array_merge([null], (new Colors('id', 'hand_hair_colors', $this->database))->values()),
											],
											'name' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('name', 'hand_hair_colors', $this->database))->values()),
											],
											'hex' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('hex', 'hand_hair_colors', $this->database))->values()),
											],
										],
										'required' => [
											'id',
											'name',
											'hex',
										],
										'type' => 'object',
									],
									'amount' => [
										'additionalProperties' => false,
										'type' => ['integer', 'null'],
									],
								],
								'required' => [
									'color',
									'amount',
								],
								'type' => 'object',
							],
						],
						'required' =>
							[
								'nails',
								'care',
								'vein_visibility',
								'joint_visibility',
								'hair',
							],
						'type' => 'object',
					],
				],
			'required' =>
				[
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
		$schema = $this->get();
		$colors = new PostgresTableEnum('id', 'colors', $this->database);
		$races = new PostgresTableEnum('id', 'races', $this->database);
		$builds = new PostgresTableEnum('id', 'body_builds', $this->database);
		$properties = &$schema['properties'];
		$properties['body'] = (new JsonEnum($builds, $properties['body'], 'build', 'build_id'))->values();
		$properties['body'] = (new JsonEnum($colors, $properties['body'], 'skin_color', 'skin_color_id'))->values();
		$properties['hair'] = (new JsonEnum($colors, $properties['hair'], 'color', 'color_id'))->values();
		$properties['face']['properties']['beard'] = (new JsonEnum($colors, $properties['face']['properties']['beard'], 'color', 'color_id'))->values();
		$properties['face']['properties']['beard'] = (new JsonEnum($colors, $properties['face']['properties']['beard'], 'color', 'color_id'))->values();
		$properties['face']['properties']['eyebrow'] = (new JsonEnum($colors, $properties['face']['properties']['eyebrow'], 'color', 'color_id'))->values();
		$schema['definitions']['eye'] = (new JsonEnum($colors, $schema['definitions']['eye'], 'color', 'color_id'))->values();
		$properties['general'] = (new JsonEnum($races, $properties['general'], 'race', 'race_id'))->values();
		$properties['hands']['properties']['nails'] = (new JsonEnum($colors, $properties['hands']['properties']['nails'], 'color', 'color_id'))->values();
		$properties['hands']['properties']['hair'] = (new JsonEnum($colors, $properties['hands']['properties']['hair'], 'color', 'color_id'))->values();
		return $schema;
	}

	public function post(): array {
		return $this->put();
	}
}