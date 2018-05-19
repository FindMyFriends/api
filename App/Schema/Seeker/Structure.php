<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Seeker;

use FindMyFriends\Schema;

final class Structure {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		$description = (new Schema\Description\Structure($this->database))->get();
		$properties = &$description['properties']['general']['properties'];
		unset($properties['age']);
		$properties['birth_year'] = [
			'type' => 'integer',
			'min' => 1850,
			'max' => (int) (new \DateTimeImmutable())->format('Y'),
		];
		unset($properties['firstname']['type'][array_search('null', $properties['firstname']['type'], true)]);
		unset($properties['lastname']['type'][array_search('null', $properties['lastname']['type'], true)]);
		array_splice($description['properties']['general']['required'], array_search('age', $description['properties']['general']['required'], true), 1, 'birth_year');
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
