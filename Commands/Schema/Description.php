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
												'id' => ['type' => 'integer'],
												'value' => ['type' => 'string'],
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
												'enum' => array_merge(
													[null],
													(new PostgresTableEnum(
														'colors',
														$this->database
													))->values()
												),
											],
											'name' => ['type' => 'string'],
											'hex' => ['type' => 'string'],
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
												'enum' => array_merge(
													[null],
													(new PostgresTableEnum(
														'colors',
														$this->database
													))->values()
												),
											],
											'name' => ['type' => 'string'],
											'hex' => ['type' => 'string'],
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
															'enum' => array_merge(
																[null],
																(new PostgresTableEnum(
																	'colors',
																	$this->database
																))->values()
															),
														],
														'name' => ['type' => 'string'],
														'hex' => ['type' => 'string'],
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
														'enum' => array_merge(
															[null],
															(new PostgresTableEnum(
																'colors',
																$this->database
															))->values()
														),
													],
													'name' => ['type' => 'string'],
													'hex' => ['type' => 'string'],
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
																	'color' =>
																		[
																			'additionalProperties' => false,
																			'properties' => [
																				'id' => [
																					'type' => ['integer', 'null'],
																					'enum' => array_merge(
																						[null],
																						(new PostgresTableEnum(
																							'colors',
																							$this->database
																						))->values()
																					),
																				],
																				'name' => ['type' => 'string'],
																				'hex' => ['type' => 'string'],
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
																	'color' =>
																		[
																			'additionalProperties' => false,
																			'properties' => [
																				'id' => [
																					'type' => ['integer', 'null'],
																					'enum' => array_merge(
																						[null],
																						(new PostgresTableEnum(
																							'colors',
																							$this->database
																						))->values()
																					),
																				],
																				'name' => ['type' => 'string'],
																				'hex' => ['type' => 'string'],
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
									'firstname' =>
										[
											'type' => ['string', 'null'],
										],
									'gender' =>
										[
											'type' => 'string',
											'enum' => (new PostgresEnum(
												'genders',
												$this->database
											))->values(),
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
													'enum' => (new PostgresTableEnum(
														'races',
														$this->database
													))->values(),
												],
												'value' => ['type' => 'string'],
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
									'color' =>
										[
											'additionalProperties' => false,
											'properties' => [
												'id' => [
													'type' => ['integer', 'null'],
													'enum' => array_merge(
														[null],
														(new PostgresTableEnum(
															'colors',
															$this->database
														))->values()
													),
												],
												'name' => ['type' => 'string'],
												'hex' => ['type' => 'string'],
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
												'enum' => array_merge(
													[null],
													(new PostgresTableEnum(
														'colors',
														$this->database
													))->values()
												),
											],
											'name' => ['type' => 'string'],
											'hex' => ['type' => 'string'],
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
		$colors = new PostgresTableEnum('colors', $this->database);
		$races = new PostgresTableEnum('races', $this->database);
		$builds = new PostgresTableEnum('body_builds', $this->database);
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