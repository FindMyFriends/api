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
												'enum' => array_merge([null], (new Colors('id', 'skin', $this->database))->values()),
											],
											'name' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('name', 'skin', $this->database))->values()),
											],
											'hex' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('hex', 'skin', $this->database))->values()),
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
												'enum' => array_merge([null], (new Colors('id', 'hair', $this->database))->values()),
											],
											'name' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('name', 'hair', $this->database))->values()),
											],
											'hex' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('hex', 'hair', $this->database))->values()),
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
									'length' => ['type' => ['integer', 'null']],
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
															'enum' => array_merge([null], (new Colors('id', 'beard', $this->database))->values()),
														],
														'name' => [
															'type' => ['string', 'null'],
															'enum' => array_merge([null], (new Colors('name', 'beard', $this->database))->values()),
														],
														'hex' => [
															'type' => ['string', 'null'],
															'enum' => array_merge([null], (new Colors('hex', 'beard', $this->database))->values()),
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
													'type' => ['integer', 'null'],
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
									'care' =>
										[
											'type' => ['integer', 'null'],
											'minimum' => 0,
											'maximum' => 10,
										],
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
														'enum' => array_merge([null], (new Colors('id', 'eyebrow', $this->database))->values()),
													],
													'name' => [
														'type' => ['string', 'null'],
														'enum' => array_merge([null], (new Colors('name', 'eyebrow', $this->database))->values()),
													],
													'hex' => [
														'type' => ['string', 'null'],
														'enum' => array_merge([null], (new Colors('hex', 'eyebrow', $this->database))->values()),
													],
												],
												'required' => [
													'id',
													'name',
													'hex',
												],
												'type' => 'object',
											],
											'care' =>
												[
													'type' => ['integer', 'null'],
													'minimum' => 0,
													'maximum' => 10,
												],
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
													'left' =>
														[
															'additionalProperties' => false,
															'properties' =>
																[
																	'id' => ['type' => 'integer'],
																	'color' => [
																		'additionalProperties' => false,
																		'properties' => [
																			'id' => [
																				'type' => ['integer', 'null'],
																				'enum' => array_merge([null], (new Colors('id', 'eye', $this->database))->values()),
																			],
																			'name' => [
																				'type' => ['string', 'null'],
																				'enum' => array_merge([null], (new Colors('name', 'eye', $this->database))->values()),
																			],
																			'hex' => [
																				'type' => ['string', 'null'],
																				'enum' => array_merge([null], (new Colors('hex', 'eye', $this->database))->values()),
																			],
																		],
																		'required' => [
																			'id',
																			'name',
																			'hex',
																		],
																		'type' => 'object',
																	],
																	'lenses' => ['type' => ['boolean', 'null']],
																],
															'required' =>
																[
																	'lenses',
																	'color',
																],
															'type' => 'object',
														],
													'right' =>
														[
															'additionalProperties' => false,
															'properties' =>
																[
																	'id' => ['type' => 'integer'],
																	'color' => [
																		'additionalProperties' => false,
																		'properties' => [
																			'id' => [
																				'type' => ['integer', 'null'],
																				'enum' => array_merge([null], (new Colors('id', 'eye', $this->database))->values()),
																			],
																			'name' => [
																				'type' => ['string', 'null'],
																				'enum' => array_merge([null], (new Colors('name', 'eye', $this->database))->values()),
																			],
																			'hex' => [
																				'type' => ['string', 'null'],
																				'enum' => array_merge([null], (new Colors('hex', 'eye', $this->database))->values()),
																			],
																		],
																		'required' => [
																			'id',
																			'name',
																			'hex',
																		],
																		'type' => 'object',
																	],
																	'lenses' => ['type' => ['boolean', 'null']],
																],
															'required' =>
																[
																	'lenses',
																	'color',
																],
															'type' => 'object',
														],
												],
										],
									'shape' => ['type' => ['string', 'null']],
									'teeth' =>
										[
											'additionalProperties' => false,
											'properties' =>
												[
													'id' => ['type' => 'integer'],
													'braces' => ['type' => ['boolean', 'null']],
													'care' =>
														[
															'type' => ['integer', 'null'],
															'minimum' => 0,
															'maximum' => 10,
														],
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
												'enum' => array_merge([null], (new Colors('id', 'nail', $this->database))->values()),
											],
											'name' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('name', 'nail', $this->database))->values()),
											],
											'hex' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('hex', 'nail', $this->database))->values()),
											],
										],
										'required' => [
											'id',
											'name',
											'hex',
										],
										'type' => 'object',
									],
									'length' =>
										[
											'type' => ['integer', 'null'],
										],
									'care' =>
										[
											'type' => ['integer', 'null'],
											'minimum' => 0,
											'maximum' => 10,
										],
								],
								'required' =>
									[
										'color',
										'length',
										'care',
									],
								'type' => 'object',
							],
							'care' => [
								'type' => ['integer', 'null'],
								'minimum' => 0,
								'maximum' => 10,
							],
							'vein_visibility' => [
								'type' => ['integer', 'null'],
								'minimum' => 0,
								'maximum' => 10,
							],
							'joint_visibility' => [
								'type' => ['integer', 'null'],
								'minimum' => 0,
								'maximum' => 10,
							],
							'hair' => [
								'additionalProperties' => false,
								'properties' => [
									'color' => [
										'additionalProperties' => false,
										'properties' => [
											'id' => [
												'type' => ['integer', 'null'],
												'enum' => array_merge([null], (new Colors('id', 'hand_hair', $this->database))->values()),
											],
											'name' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('name', 'hand_hair', $this->database))->values()),
											],
											'hex' => [
												'type' => ['string', 'null'],
												'enum' => array_merge([null], (new Colors('hex', 'hand_hair', $this->database))->values()),
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
		$properties['face']['properties']['eye']['properties']['left'] = (new JsonEnum($colors, $properties['face']['properties']['eye']['properties']['left'], 'color', 'color_id'))->values();
		$properties['face']['properties']['eye']['properties']['right'] = (new JsonEnum($colors, $properties['face']['properties']['eye']['properties']['right'], 'color', 'color_id'))->values();
		$properties['general'] = (new JsonEnum($races, $properties['general'], 'race', 'race_id'))->values();
		$properties['hands']['properties']['nails'] = (new JsonEnum($colors, $properties['hands']['properties']['nails'], 'color', 'color_id'))->values();
		$properties['hands']['properties']['hair'] = (new JsonEnum($colors, $properties['hands']['properties']['hair'], 'color', 'color_id'))->values();
		return $schema;
	}

	public function post(): array {
		return $this->put();
	}
}