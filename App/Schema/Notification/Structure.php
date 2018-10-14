<?php
declare(strict_types = 1);

namespace FindMyFriends\Schema\Notification;

use FindMyFriends\Schema;
use Klapuch\Storage;

final class Structure {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function get(): array {
		return [
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'additionalProperties' => false,
			'properties' => [
				'id' => ['type' => ['integer', 'null']],
				'seeker_id' => ['type' => 'integer'],
				'involved_seeker_id' => ['type' => ['integer', 'null']],
				'seen' => ['type' => 'boolean'],
				'seen_at' => ['type' => ['string', 'null'], 'format' => 'date-time'],
				'notified_at' => ['type' => 'string', 'format' => 'date-time'],
				'type' => [
					'type' => 'string',
					'enum' => (new Schema\PostgresConstant('notification_types', $this->connection))->values(),
				],
			],
			'required' => [
				'id',
				'seeker_id',
				'involved_seeker_id',
				'seen',
				'seen_at',
				'notified_at',
				'type',
			],
			'type' => 'object',
		];
	}

	public function patch(): array {
		$schema = $this->get();
		$properties = &$schema['properties'];
		unset(
			$properties['id'],
			$properties['seeker_id'],
			$properties['involved_seeker_id'],
			$properties['seen_at'],
			$properties['notified_at'],
			$properties['type']
		);
		unset($schema['required']);
		return $schema;
	}
}
