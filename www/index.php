<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Configuration\CreatedHashids;
use Klapuch\Application;
use Klapuch\Configuration;
use Klapuch\Log;
use Klapuch\Output;
use Klapuch\Routing;
use Klapuch\Storage;
use Klapuch\Uri;

const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini',
	SECRET_CONFIGURATION = __DIR__ . '/../App/Configuration/.secrets.ini',
	HASHIDS_CONFIGURATION = __DIR__ . '/../App/Configuration/.hashids.json',
	HASHIDS_SECRET_CONFIGURATION = __DIR__ . '/../App/Configuration/.hashids.secret.json',
	LOGS = __DIR__ . '/../log',
	LOG_FILE = LOGS . '/logs.log';

$uri = new Uri\CachedUri(
	new Uri\BaseUrl(
		$_SERVER['SCRIPT_NAME'],
		$_SERVER['REQUEST_URI'],
		$_SERVER['SERVER_NAME'],
		$_SERVER['HTTPS'] ?? 'http'
	)
);

$configuration = (new Configuration\CombinedSource(
	new Configuration\ValidIni(new SplFileInfo(CONFIGURATION)),
	new Configuration\ValidIni(new SplFileInfo(SECRET_CONFIGURATION)),
	new Configuration\NamedSource(
		'HASHIDS',
		new CreatedHashids(
			new Configuration\CombinedSource(
				new Configuration\ValidJson(new SplFileInfo(HASHIDS_CONFIGURATION)),
				new Configuration\ValidJson(new SplFileInfo(HASHIDS_SECRET_CONFIGURATION))
			)
		)
	)
))->read();

$redis = new Predis\Client($configuration['REDIS']['uri']);
$elasticsearch = Elasticsearch\ClientBuilder::create()
	->setHosts($configuration['ELASTICSEARCH']['hosts'])
	->build();

echo (new class(
	new Log\ChainedLogs(
		new Log\FilesystemLogs(new Log\DynamicLocation(LOGS)),
		new Log\FilesystemLogs(new SplFileInfo(LOG_FILE))
	),
	new Routing\MatchingRoutes(
		new Routing\MappedRoutes(
			new Routing\QueryRoutes(
				new Routing\PathRoutes(
					new Routing\ShortcutRoutes(
						new Routing\HttpMethodRoutes(
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
				return $destination->template(
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
			http_response_code(HTTP_INTERNAL_SERVER_ERROR);
			exit;
		}
	}
})->render();