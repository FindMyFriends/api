<?php
declare(strict_types = 1);

namespace FindMyFriends\Log;

use Klapuch\Log;
use Klapuch\Storage;

/**
 * Logs stored in postgres
 */
final class PostgresLogs implements Log\Logs {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function put(\Throwable $exception, Log\Environment $environment): void {
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO log.logs (message, trace, post, get, session, cookie, server, input) VALUES
			(?, ?, ?, ?, ?, ?, ?, ?)',
			[
				$exception->getMessage(),
				$exception->getTraceAsString(),
				json_encode($environment->post()),
				json_encode($environment->get()),
				json_encode($environment->session()),
				json_encode($environment->cookie()),
				json_encode($environment->server()),
				$environment->input(),
			]
		))->execute();
	}
}
