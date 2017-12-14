<?php
declare(strict_types = 1);
namespace FindMyFriends\Http;

use Klapuch\Storage;
use Klapuch\Uri;

/**
 * ETag stored in postgres database
 */
final class PostgresETag implements ETag {
	private $database;
	private $uri;

	public function __construct(\PDO $database, Uri\Uri $uri) {
		$this->database = $database;
		$this->uri = $uri;
	}

	public function exists(): bool {
		return (bool) (new Storage\NativeQuery(
			$this->database,
			'SELECT 1 FROM http.etags WHERE entity = LOWER(?)',
			[$this->uri->path()]
		))->field();
	}

	public function get(): string {
		return (new Storage\NativeQuery(
			$this->database,
			'SELECT tag FROM http.etags WHERE entity = LOWER(?)',
			[$this->uri->path()]
		))->field();
	}

	public function set(object $entity): ETag {
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO http.etags (entity, tag, created_at) VALUES (?, ?, NOW())
			ON CONFLICT (LOWER(entity)) DO UPDATE
			SET tag = EXCLUDED.tag, created_at = EXCLUDED.created_at',
			[$this->uri->path(), $this->tag($entity)]
		))->execute();
		return new self($this->database, $this->uri);
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