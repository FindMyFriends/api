<?php
declare(strict_types = 1);
namespace FindMyFriends\Commands\Schema;

final class Evolution {
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
					'evolved_at' =>
						[
							'id' => '/properties/created_at',
							'type' => 'string',
						],
					'id' =>
						[
							'id' => '/properties/id',
							'type' => 'integer',
						],
					'body' =>
						[
							'id' => '/properties/body',
							'additionalProperties' => false,
							'properties' =>
								[
									'build' =>
										[
											'id' => '/properties/body/properties/build',
											'type' =>
												[
													'string',
													'null',
												],
										],
									'height' =>
										[
											'id' => '/properties/body/properties/height',
											'type' =>
												[
													'integer',
													'null',
												],
										],
									'skin' =>
										[
											'id' => '/properties/body/properties/skin',
											'type' =>
												[
													'string',
													'null',
												],
										],
									'weight' =>
										[
											'id' => '/properties/body/properties/weight',
											'type' =>
												[
													'integer',
													'null',
												],
										],
								],
							'required' =>
								[
									'skin',
									'weight',
									'build',
									'height',
								],
							'type' => 'object',
						],
					'face' =>
						[
							'id' => '/properties/face',
							'additionalProperties' => false,
							'properties' =>
								[
									'acne' =>
										[
											'id' => '/properties/face/properties/acne',
											'type' =>
												[
													'boolean',
													'null',
												],
										],
									'beard' =>
										[
											'id' => '/properties/face/properties/beard',
											'type' =>
												[
													'string',
													'null',
												],
										],
									'complexion' =>
										[
											'id' => '/properties/face/properties/complexion',
											'type' =>
												[
													'string',
													'null',
												],
										],
									'eyebrow' =>
										[
											'id' => '/properties/face/properties/eyebrow',
											'type' =>
												[
													'string',
													'null',
												],
										],
									'freckles' =>
										[
											'id' => '/properties/face/properties/freckles',
											'type' =>
												[
													'boolean',
													'null',
												],
										],
									'hair' =>
										[
											'id' => '/properties/face/properties/hair',
											'additionalProperties' => false,
											'properties' =>
												[
													'color' =>
														[
															'id' => '/properties/face/properties/hair/properties/color',
															'type' =>
																[
																	'string',
																	'null',
																],
														],
													'highlights' =>
														[
															'id' => '/properties/face/properties/hair/properties/highlights',
															'type' =>
																[
																	'boolean',
																	'null',
																],
														],
													'length' =>
														[
															'id' => '/properties/face/properties/hair/properties/length',
															'type' =>
																[
																	'integer',
																	'null',
																],
														],
													'nature' =>
														[
															'id' => '/properties/face/properties/hair/properties/nature',
															'type' =>
																[
																	'boolean',
																	'null',
																],
														],
													'roots' =>
														[
															'id' => '/properties/face/properties/hair/properties/roots',
															'type' =>
																[
																	'boolean',
																	'null',
																],
														],
													'style' =>
														[
															'id' => '/properties/face/properties/hair/properties/style',
															'type' =>
																[
																	'string',
																	'null',
																],
														],
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
									'eye' =>
										[
											'id' => '/properties/face/properties/eye',
											'additionalProperties' => false,
											'properties' =>
												[
													'left' =>
														[
															'id' => '/properties/face/properties/eye/left',
															'additionalProperties' => false,
															'properties' =>
																[
																	'color' =>
																		[
																			'id' => '/properties/face/properties/eye/left/properties/color',
																			'type' =>
																				[
																					'string',
																					'null',
																				],
																		],
																	'lenses' =>
																		[
																			'id' => '/properties/face/properties/eye/left/properties/lenses',
																			'type' =>
																				[
																					'boolean',
																					'null',
																				],
																		],
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
															'id' => '/properties/face/properties/eye/right',
															'additionalProperties' => false,
															'properties' =>
																[
																	'color' =>
																		[
																			'id' => '/properties/face/properties/eye/right/properties/color',
																			'type' =>
																				[
																					'string',
																					'null',
																				],
																		],
																	'lenses' =>
																		[
																			'id' => '/properties/face/properties/eye/right/properties/lenses',
																			'type' =>
																				[
																					'boolean',
																					'null',
																				],
																		],
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
									'shape' =>
										[
											'id' => '/properties/face/properties/shape',
											'type' =>
												[
													'string',
													'null',
												],
										],
									'teeth' =>
										[
											'id' => '/properties/face/properties/teeth',
											'additionalProperties' => false,
											'properties' =>
												[
													'braces' =>
														[
															'id' => '/properties/face/properties/teeth/properties/braces',
															'type' =>
																[
																	'boolean',
																	'null',
																],
														],
													'care' =>
														[
															'id' => '/properties/face/properties/teeth/properties/care',
															'type' =>
																[
																	'string',
																	'null',
																],
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
									'complexion',
									'shape',
									'eye',
									'hair',
									'eyebrow',
									'acne',
									'freckles',
								],
							'type' => 'object',
						],
					'general' =>
						[
							'id' => '/properties/general',
							'additionalProperties' => false,
							'properties' =>
								[
									'age' =>
										[
											'id' => '/properties/general/properties/age',
											'additionalProperties' => false,
											'properties' =>
												[
													'from' =>
														[
															'id' => '/properties/general/properties/age/properties/from',
															'type' =>
																[
																	'integer',
																	'null',
																],
															'minimum' => 15,
															'maximum' => 130,
														],
													'to' =>
														[
															'id' => '/properties/general/properties/age/properties/to',
															'type' =>
																[
																	'integer',
																	'null',
																],
															'minimum' => 15,
															'maximum' => 130,
														],
												],
											'required' =>
												[
													'from',
													'to',
												],
											'type' => 'object',
										],
									'firstname' =>
										[
											'id' => '/properties/general/properties/firstname',
											'type' =>
												[
													'string',
													'null',
												],
										],
									'gender' =>
										[
											'id' => '/properties/general/properties/gender',
											'type' => 'string',
											'enum' => (new PostgresEnum(
												'genders',
												$this->database
											))->values(),
										],
									'lastname' =>
										[
											'id' => '/properties/general/properties/lastname',
											'type' =>
												[
													'string',
													'null',
												],
										],
									'race' =>
										[
											'id' => '/properties/general/properties/race',
											'type' => 'string',
											'enum' => (new PostgresEnum(
												'races',
												$this->database
											))->values(),
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
						'id' => '/properties/hands',
						'additionalProperties' => false,
						'properties' => [
							'nails' => [
								'id' => '/properties/hands/nails',
								'additionalProperties' => false,
								'properties' => [
									'color' =>
										[
											'id' => '/properties/hands/properties/nails/properties/color',
											'type' => ['string', 'null'],
											'enum' => array_merge(
												[null],
												(new PostgresEnum(
													'colors',
													$this->database
												))->values()
											),
										],
									'length' =>
										[
											'id' => '/properties/hands/properties/nails/properties/length',
											'type' => ['integer', 'null'],
										],
									'care' =>
										[
											'id' => '/properties/hands/properties/nails/properties/care',
											'type' => ['string', 'null'],
											'enum' => array_merge(
												[null],
												(new PostgresEnum(
													'care',
													$this->database
												))->values()
											),
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
								'id' => '/properties/hands/care',
								'type' => ['string', 'null'],
								'enum' => array_merge(
									[null],
									(new PostgresEnum(
										'hand_care',
										$this->database
									))->values()
								),
							],
							'veins' => [
								'id' => '/properties/hands/veins',
								'type' => ['string', 'null'],
								'enum' => array_merge(
									[null],
									(new PostgresEnum(
										'vein_visibility',
										$this->database
									))->values()
								),
							],
							'joint' => [
								'id' => '/properties/hands/joint',
								'type' => ['string', 'null'],
								'enum' => array_merge(
									[null],
									(new PostgresEnum(
										'joint_visibility',
										$this->database
									))->values()
								),
							],
							'hair' => [
								'id' => '/properties/hands/hair',
								'type' => ['string', 'null'],
								'enum' => (new PostgresEnum(
									'hand_hair',
									$this->database
								))->values(),
							],
						],
						'required' =>
							[
								'nails',
								'care',
								'veins',
								'joint',
								'hair',
							],
						'type' => 'object',
					],
				],
			'required' =>
				[
					'general',
					'face',
					'body',
					'hands',
				],
			'type' => 'object',
		];
	}

	public function put(): array {
		$schema = $this->get();
		unset($schema['properties']['id']);
		return $schema;
	}

	public function post(): array {
		$schema = $this->put();
		unset($schema['properties']['general']['properties']['age']);
		unset(
			$schema['properties']['general']['required'][array_search(
				'age',
				$schema['properties']['general']['required']
			)]
		);
		$schema['body']['default'] = [
			'build' => null,
			'height' => null,
			'skin' => null,
			'weight' => null,
		];
		$schema['face']['default'] = [
			'acne' => null,
			'beard' => null,
			'complexion' => null,
			'eyebrow' => null,
			'freckles' => null,
			'hair' => [
				'color' => null,
				'highlights' => null,
				'length' => null,
				'nature' => null,
				'roots' => null,
				'style' => null,
			],
			'eye' => [
				'left' => [
					'color' => null,
					'lenses' => null,
				],
				'right' => [
					'color' => null,
					'lenses' => null,
				],
			],
			'shape' => null,
			'teeth' => [
				'braces' => null,
				'care' => null,
			],
		];
		$schema['hands']['default'] = [
			'nails' => [
				'color' => null,
				'length' => null,
				'care' => null,
			],
			'care' => null,
			'veins' => null,
			'joint' => null,
			'hair' => null,
		];
		$schema['general']['firstname']['default'] = null;
		$schema['general']['lastname']['default'] = null;
		return $schema;
	}
}