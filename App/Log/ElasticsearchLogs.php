<?php
declare(strict_types = 1);

namespace FindMyFriends\Log;

use FindMyFriends\Elasticsearch\LazyElasticsearch;
use Klapuch\Log;

/**
 * Logs stored in elasticsearch
 */
final class ElasticsearchLogs implements Log\Logs {
	private const OPTIONS = [
		'index' => 'logs',
		'type' => 'pile',
	];

	/** @var \FindMyFriends\Elasticsearch\LazyElasticsearch */
	private $elasticsearch;

	public function __construct(LazyElasticsearch $elasticsearch) {
		$this->elasticsearch = $elasticsearch;
	}

	public function put(\Throwable $exception, Log\Environment $environment): void {
		$this->elasticsearch->create()->index(self::OPTIONS + ['body' => $this->body($exception, $environment)]);
	}

	private function body(\Throwable $exception, Log\Environment $environment): array {
		return (new Log\CompleteLog(
			new Log\ExceptionLog($exception),
			new Log\ExceptionsLog($exception),
			new Log\EnvironmentLog($environment)
		))->content();
	}
}
