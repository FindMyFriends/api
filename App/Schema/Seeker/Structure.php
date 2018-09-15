<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Seeker;

use FindMyFriends\Schema;
use Klapuch\Storage;

final class Structure {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(Storage\MetaPDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		$description = (new Schema\Description\Structure($this->database))->get();
		$properties = &$description['properties']['general']['properties'];
		unset($properties['age']);
		$properties['birth_year'] = [
			'type' => 'integer',
			'minimum' => (new Storage\TypedQuery($this->database, 'SELECT constant.birth_year_min()'))->field(),
			'maximum' => (new Storage\TypedQuery($this->database, 'SELECT constant.birth_year_max()'))->field(),
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
				'password' => ['type' => 'string'],
			] + ['general' => $description['properties']['general']],
			'required' => ['email', 'password', 'general'],
			'type' => 'object',
		];
	}

	public function post(): array {
		return $this->get();
	}
}
