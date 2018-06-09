<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Evolution;

use FindMyFriends\Schema;

final class Structure {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		$description = (new Schema\Description\Structure($this->database))->get();
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'evolved_at' => ['type' => 'string', 'format' => 'date-time'],
				'seeker_id' => ['type' => 'integer'],
				'id' => ['type' => 'string'],
			] + $description['properties'],
			'required' => $description['required'],
			'type' => 'object',
		] + $description;
	}

	public function put(): array {
		$schema = $this->get();
		$description = (new Schema\Description\Structure($this->database))->put();
		$schema['properties'] = $description['properties'] + $schema['properties'];
		$schema['definitions'] = $description['definitions'] + $schema['definitions'];
		$properties = &$schema['properties'];
		unset($properties['seeker_id']);
		unset($properties['id']);
		return $schema;
	}

	public function post(): array {
		$schema = $this->put();
		$properties = &$schema['properties'];
		unset($properties['general']['properties']['age']);
		unset($properties['general']['required'][array_search('age', $properties['general']['required'], true)]);
		$properties['general']['required'] = array_values($properties['general']['required']);
		return $schema;
	}
}
