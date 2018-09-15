<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Configuration;
use FindMyFriends\Elasticsearch\LazyElasticsearch;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Log;
use Klapuch\Output;
use Klapuch\Routing;
use Klapuch\Storage;
use Klapuch\Uri;

$uri = new Uri\CachedUri(
	new Uri\BaseUrl(
		$_SERVER['SCRIPT_NAME'],
		$_SERVER['REQUEST_URI'],
		$_SERVER['SERVER_NAME'],
		isset($_SERVER['HTTPS']) ? 'https' : 'http'
	)
);

$configuration = (new Configuration\ApplicationConfiguration())->read();

$redis = new Predis\Client($configuration['REDIS']['uri']);
$elasticsearch = new LazyElasticsearch($configuration['ELASTICSEARCH']['hosts']);

echo (new class(
	$configuration,
	new Log\ChainedLogs(
		new FindMyFriends\Log\FilesystemLogs(
			new Log\DynamicLocation($configuration['LOGS']['directory'])
		),
		new FindMyFriends\Log\ElasticsearchLogs($elasticsearch)
	),
	new Routing\MatchingRoutes(
		new FindMyFriends\Routing\NginxMatchedRoutes(
			new FindMyFriends\Routing\ApplicationRoutes(
				$uri,
				new Storage\MetaPDO(
					new Storage\SideCachedPDO(
						new Storage\SafePDO(
							$configuration['DATABASE']['dsn'],
							$configuration['DATABASE']['user'],
							$configuration['DATABASE']['password']
						)
					),
					$redis
				),
				$redis,
				$elasticsearch,
				new PhpAmqpLib\Connection\AMQPLazyConnection(
					$configuration['RABBITMQ']['host'],
					$configuration['RABBITMQ']['port'],
					$configuration['RABBITMQ']['user'],
					$configuration['RABBITMQ']['pass'],
					$configuration['RABBITMQ']['vhost']
				),
				new Encryption\AES256CBC($configuration['KEYS']['password']),
				$configuration['HASHIDS']
			)
		),
		$uri,
		$_SERVER['REQUEST_METHOD']
	)
) implements Output\Template {
	/** @var mixed[] */
	private $configuration;

	/** @var \Klapuch\Routing\Routes */
	private $routes;

	/** @var \Klapuch\Log\Logs */
	private $logs;

	public function __construct(
		array $configuration,
		Log\Logs $logs,
		Routing\Routes $routes
	) {
		$this->configuration = $configuration;
		$this->routes = $routes;
		$this->logs = $logs;
	}

	public function render(array $variables = []): string {
		try {
			$match = $this->routes->matches();
			/** @var \Closure $destination */
			[$source, $destination] = [key($match), current($match)];
			return (new Application\RawTemplate(
				$destination()->response(
					(new FindMyFriends\Routing\SuitedHashIdMask(
						$this->configuration['ROUTES'][$source]['types'] ?? [],
						new Routing\TypedMask(
							new Routing\CombinedMask(
								new FindMyFriends\Routing\NginxMask(),
								new FindMyFriends\Routing\CommonMask()
							)
						),
						$this->configuration['HASHIDS']
					))->parameters()
				)
			))->render();
		} catch (\Throwable $e) {
			// TODO: Add logging
			// $this->logs->put($e, new Log\CurrentEnvironment());
			if ($e instanceof \UnexpectedValueException) {
				return (new Application\RawTemplate(
					new FindMyFriends\Response\JsonError($e)
				))->render();
			}
			return (new Application\RawTemplate(
				new FindMyFriends\Response\JsonError(
					new \UnexpectedValueException(),
					[],
					HTTP_INTERNAL_SERVER_ERROR
				)
			))->render();
		}
	}
})->render();
