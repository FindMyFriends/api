<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Seeker;

use FindMyFriends\Schema;
use Klapuch\Storage;

final class Structure {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function get(): array {
		$description = (new Schema\Description\Structure($this->connection))->get();
		$properties = &$description['properties']['general']['properties'];
		unset($properties['age']);
		$properties['birth_year'] = [
			'type' => 'integer',
			'minimum' => (new Storage\TypedQuery($this->connection, 'SELECT constant.birth_year_range_min()'))->field(),
			'maximum' => (new Storage\TypedQuery($this->connection, 'SELECT constant.birth_year_range_max()'))->field(),
		];
		unset($properties['firstname']['type'][array_search('null', $properties['firstname']['type'], true)]);
		unset($properties['lastname']['type'][array_search('null', $properties['lastname']['type'], true)]);
		$age = array_search('age', $description['properties']['general']['required'], true);
		$description['properties']['general']['required'][] = 'birth_year';
		unset($description['properties']['general']['required'][$age]);
		$description['properties']['general']['required'] = array_values($description['properties']['general']['required']);
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'email' => ['type' => 'string'],
			] + ['general' => $description['properties']['general']] + [
				'contact' => [
					'type' => 'object',
					'additionalProperties' => false,
					'required' => ['facebook', 'instagram', 'phone_number'],
					'properties' => [
						'facebook' => ['type' => ['string', 'null']],
						'instagram' => ['type' => ['string', 'null']],
						'phone_number' => ['type' => ['string', 'null']],
					],
				],
			],
			'required' => ['email', 'general', 'contact'],
			'type' => 'object',
		];
	}

	public function post(): array {
		$get = $this->get();
		$get['properties'] = ['password' => ['type' => 'string']] + $get['properties'];
		$get['required'] = array_merge($get['required'], ['password']);
		return $get;
	}
}
