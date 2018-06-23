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
		$this->elasticsearch->index(self::OPTIONS + ['body' => $this->body($exception, $environment, new \DateTimeImmutable())]);
	}

	private function body(\Throwable $exception, Log\Environment $environment, \DateTimeInterface $now): array {
		return [
			'logged_at' => $now->format('Y-m-d H:i'),
			'message' => $exception->getMessage(),
			'trace' => $exception->getTraceAsString(),
			'cookie' => $environment->cookie(),
			'get' => $environment->get(),
			'input' => $environment->input(),
			'post' => $environment->post(),
			'server' => $environment->server(),
			'session' => $environment->session(),
		];
	}
}
