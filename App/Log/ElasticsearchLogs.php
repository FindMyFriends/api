<?php
declare(strict_types = 1);

namespace FindMyFriends\Log;

use Elasticsearch;
use Klapuch\Log;

/**
 * Logs stored in elasticsearch
 */
final class ElasticsearchLogs implements Log\Logs {
	private const OPTIONS = [
		'index' => 'logs',
		'type' => 'pile',
	];
	private $elasticsearch;

	public function __construct(Elasticsearch\Client $elasticsearch) {
		$this->elasticsearch = $elasticsearch;
	}

	public function put(\Throwable $exception, Log\Environment $environment): void {
		$this->elasticsearch->index(self::OPTIONS + ['body' => $this->body($exception, $environment)]);
	}

	private function body(\Throwable $exception, Log\Environment $environment): array {
		return (new Log\CompleteLog(
			new Log\ExceptionLog($exception),
			new Log\ExceptionsLog($exception),
			new Log\EnvironmentLog($environment)
		))->content();
	}
}
