<?php
declare(strict_types = 1);

namespace FindMyFriends\Http;

use Klapuch\Storage;
use Klapuch\Uri;

/**
 * ETag stored in postgres connection
 */
final class PostgresETag implements ETag {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Klapuch\Uri\Uri */
	private $uri;

	public function __construct(Storage\Connection $connection, Uri\Uri $uri) {
		$this->connection = $connection;
		$this->uri = $uri;
	}

	public function exists(): bool {
		return (bool) (new Storage\NativeQuery(
			$this->connection,
			'SELECT 1 FROM etags WHERE entity = LOWER(?)',
			[$this->uri->path()]
		))->field();
	}

	public function get(): string {
		return (new Storage\NativeQuery(
			$this->connection,
			'SELECT tag FROM etags WHERE entity = LOWER(?)',
			[$this->uri->path()]
		))->field();
	}

	public function set(object $entity): ETag {
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO etags (entity, tag, created_at) VALUES (?, ?, NOW())
			ON CONFLICT (LOWER(entity)) DO UPDATE
			SET tag = EXCLUDED.tag, created_at = EXCLUDED.created_at',
			[$this->uri->path(), $this->tag($entity)]
		))->execute();
		return new self($this->connection, $this->uri);
	}

	private function tag(object $entity): string {
		return sprintf(
			'"%s"',
			md5(
				(new \ReflectionClass($entity))->isAnonymous()
					? spl_object_hash($entity)
					: serialize($entity)
			)
		);
	}
}
