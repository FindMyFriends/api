<?php
declare(strict_types = 1);
namespace FindMyFriends\Commands\Schema;

final class Demand {
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
					'created_at' =>
						[
							'id' => '/properties/created_at',
							'type' => 'string',
						],
					'seeker_id' =>
						[
							'id' => '/properties/seeker_id',
							'type' => 'integer',
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
											'enum' => (new PostgresEnum('genders', $this->database))->values(),
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
											'enum' => (new PostgresEnum('races', $this->database))->values(),
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
					'location' =>
						[
							'id' => '/properties/location',
							'additionalProperties' => false,
							'properties' =>
								[
									'coordinates' =>
										[
											'id' => '/properties/location/properties/coordinates',
											'additionalProperties' => false,
											'properties' =>
												[
													'latitude' =>
														[
															'id' => '/properties/location/properties/coordinates/properties/latitude',
															'type' => 'number',
														],
													'longitude' =>
														[
															'id' => '/properties/location/properties/coordinates/properties/longitude',
															'type' => 'number',
														],
												],
											'required' =>
												[
													'latitude',
													'longitude',
												],
											'type' => 'object',
										],
									'met_at' =>
										[
											'id' => '/properties/location/properties/met_at',
											'additionalProperties' => false,
											'properties' =>
												[
													'from' =>
														[
															'id' => '/properties/location/properties/met_at/properties/from',
															'type' =>
																[
																	'string',
																	'null',
																],
														],
													'to' =>
														[
															'id' => '/properties/location/properties/met_at/properties/to',
															'type' =>
																[
																	'string',
																	'null',
																],
														],
												],
											'required' =>
												[
													'from',
													'to',
												],
											'type' => 'object',
										],
								],
							'required' =>
								[
									'coordinates',
									'met_at',
								],
							'type' => 'object',
						],
				],
			'required' =>
				[
					'general',
					'face',
					'body',
					'location',
				],
			'type' => 'object',
		];
	}

	public function put(): array {
		$schema = $this->get();
		unset($schema['properties']['created_at']);
		unset($schema['properties']['seeker_id']);
		unset($schema['properties']['id']);
		return $schema;
	}

	public function post(): array {
		$schema = $this->put();
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
		$schema['general']['firstname']['default'] = null;
		$schema['general']['lastname']['default'] = null;
		return $schema;
	}
}