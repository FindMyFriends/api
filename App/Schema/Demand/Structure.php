<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Demand;

use FindMyFriends\Schema;

final class Structure {
	/** @var \PDO */
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function get(): array {
		$description = (new Schema\Description\Structure($this->database))->get();
		$location = (new Schema\Location\Structure($this->database))->post();
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'created_at' => ['type' => 'string'],
				'note' => ['type' => ['string', 'null']],
				'seeker_id' => ['type' => 'integer'],
				'id' => ['type' => 'string'],
				'location' => ['properties' => $location['properties']],
			] + $description['properties'],
			'required' => array_merge(
				['created_at', 'note', 'seeker_id', 'id', 'location'],
				$description['required']
			),
			'type' => 'object',
		] + $description;
	}

	public function patch(): array {
		['properties' => ['note' => $noteType]] = $this->get();
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => ['note' => $noteType],
			'anyOf' => [
				['required' => ['note']],
			],
			'type' => 'object',
		];
	}

	public function put(): array {
		$schema = $this->get();
		$description = (new Schema\Description\Structure($this->database))->put();
		$schema['properties'] = $description['properties'] + $schema['properties'];
		$schema['definitions'] = $description['definitions'] + $schema['definitions'];
		$properties = &$schema['properties'];
		$required = &$schema['required'];
		unset($properties['created_at'], $properties['seeker_id'], $properties['id']);
		unset($required[array_search('created_at', $required, true)]);
		unset($required[array_search('seeker_id', $required, true)]);
		unset($required[array_search('id', $required, true)]);
		$required = array_values($required);
		return $schema;
	}

	public function post(): array {
		return $this->put();
	}
}
