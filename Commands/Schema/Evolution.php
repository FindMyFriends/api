<?php
declare(strict_types = 1);
namespace FindMyFriends\Commands\Schema;

final class Evolution {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		$description = (new Description($this->database))->get();
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' =>
				[
					'evolved_at' => ['type' => 'string'],
					'id' => ['type' => 'integer'],
				] + $description['properties'],
			'required' => $description['required'],
			'type' => 'object',
		] + $description;
	}

	public function put(): array {
		$schema = $this->get();
		$description = (new Description($this->database))->put();
		$schema['properties'] = $description['properties'] + $schema['properties'];
		$properties = &$schema['properties'];
		unset($properties['seeker_id']);
		unset($properties['general']['properties']['age']);
		unset($properties['general']['required'][array_search('age', $properties['general']['required'], true)]);
		return $schema;
	}

	public function post(): array {
		return $this->put();
	}
}