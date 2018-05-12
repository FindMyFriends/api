<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Configuration;
use Klapuch\Application;
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
		$_SERVER['HTTPS'] ?? 'http'
	)
);

$configuration = (new Configuration\ApplicationConfiguration())->read();

$redis = new Predis\Client($configuration['REDIS']['uri']);
$elasticsearch = Elasticsearch\ClientBuilder::create()
	->setHosts($configuration['ELASTICSEARCH']['hosts'])
	->build();
$database = new Storage\MetaPDO(
	new Storage\SideCachedPDO(
		new Storage\SafePDO(
			$configuration['DATABASE']['dsn'],
			$configuration['DATABASE']['user'],
			$configuration['DATABASE']['password']
		)
	),
	$redis
);
$rabbitMq = new PhpAmqpLib\Connection\AMQPLazyConnection(
	$configuration['RABBITMQ']['host'],
	$configuration['RABBITMQ']['port'],
	$configuration['RABBITMQ']['user'],
	$configuration['RABBITMQ']['pass'],
	$configuration['RABBITMQ']['vhost']
);

echo (new class(
	new Log\ChainedLogs(
		new Log\FilesystemLogs(new Log\DynamicLocation(sprintf('%s/../%s', __DIR__, $configuration['LOGS']['directory']))),
		new Log\FilesystemLogs(new SplFileInfo(sprintf('%s/../%s', __DIR__, $configuration['LOGS']['file'])))
	),
	new Routing\MatchingRoutes(
		new Routing\MappedRoutes(
			new Routing\QueryRoutes(
				new Routing\PathRoutes(
					new Routing\ShortcutRoutes(
						new Routing\HttpMethodRoutes(
							new FindMyFriends\Routing\ApplicationRoutes(
								$uri,
								$database,
								$redis,
								$elasticsearch,
								$rabbitMq,
								$configuration['HASHIDS']
							),
							$_SERVER['REQUEST_METHOD']
						)
					),
					$uri
				),
				$uri
			),
			function(array $match) use ($uri, $configuration): Output\Template {
				/** @var \Klapuch\Application\View $destination */
				[$source, $destination] = [key($match), current($match)];
				return new Application\RawTemplate(
					$destination->response(
						(new FindMyFriends\Routing\SuitedHashIdMask(
							new Routing\TypedMask(
								new Routing\CombinedMask(
									new Routing\PathMask($source, $uri),
									new Routing\QueryMask($source, $uri)
								)
							),
							$configuration['HASHIDS'],
							$source
						))->parameters()
					)
				);
			}
		),
		$uri,
		$_SERVER['REQUEST_METHOD']
	)
) implements Output\Template {
	private $logs;
	private $routes;

	public function __construct(Log\Logs $logs, Routing\Routes $routes) {
		$this->logs = $logs;
		$this->routes = $routes;
	}

	public function render(array $variables = []): string {
		try {
			return current($this->routes->matches())->render($variables);
		} catch (\UnexpectedValueException $ex) {
			return (new Application\RawTemplate(
				new FindMyFriends\Response\JsonError($ex, [])
			))->render();
		} catch (\Throwable $ex) {
			$this->logs->put(
				new Log\PrettyLog(
					$ex,
					new Log\PrettySeverity(
						new Log\JustifiedSeverity(Log\Severity::ERROR)
					)
				)
			);
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
