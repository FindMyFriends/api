<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Http;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Configuration;
use Klapuch\Log;
use Klapuch\Output;
use Klapuch\Routing;
use Klapuch\Storage;
use Klapuch\Uri;

const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini',
	LOCAL_CONFIGURATION = __DIR__ . '/../App/Configuration/.config.local.ini',
	LOGS = __DIR__ . '/../log';

$uri = new Uri\CachedUri(
	new Uri\BaseUrl(
		$_SERVER['SCRIPT_NAME'],
		$_SERVER['REQUEST_URI'],
		$_SERVER['SERVER_NAME'],
		$_SERVER['HTTPS'] ?? 'http'
	)
);

$configuration = (new Configuration\CachedSource(
	new Configuration\CombinedSource(
		new Configuration\ValidIni(new SplFileInfo(CONFIGURATION)),
		new Configuration\ValidIni(new SplFileInfo(LOCAL_CONFIGURATION))
	)
))->read();

$hashids = new Hashids\Hashids($configuration['HASHID']['salt']);

echo (new class(
	new Log\FilesystemLogs(new Log\DynamicLocation(new Log\DirectoryLocation(LOGS))),
	new Routing\MatchingRoutes(
		new Routing\MappedRoutes(
			new Routing\QueryRoutes(
				new Routing\PathRoutes(
					new Routing\ShortcutRoutes(
						new Routing\HttpMethodRoutes(
							new class(
								$uri,
								new Storage\MetaPDO(
									new Storage\SideCachedPDO(
										new Storage\SafePDO(
											$configuration['DATABASE']['dsn'],
											$configuration['DATABASE']['user'],
											$configuration['DATABASE']['password']
										)
									),
									new Predis\Client($configuration['REDIS']['uri'])
								),
								$hashids
							) implements Routing\Routes {
								private $uri;
								private $database;
								private $hashids;

								public function __construct(Uri\Uri $uri, \PDO $database, Hashids\HashidsInterface $hashids) {
									$this->uri = $uri;
									$this->database = $database;
									$this->hashids = $hashids;
								}

								public function matches(): array {
									$user = (new Access\ApiEntrance(
										$this->database
									))->enter((new Application\PlainRequest())->headers());
									return [
										'v1/demands?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?\w+) [GET]' => new V1\Demands\Get(
											$this->hashids,
											$this->uri,
											$this->database,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/demands/{id} [GET]' => new V1\Demand\Get(
											$this->hashids,
											$this->uri,
											$this->database,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/demands [POST]' => new V1\Demands\Post(
											$this->hashids,
											new Application\PlainRequest(),
											$this->uri,
											$this->database,
											$user
										),
										'v1/demands/{id} [PUT]' => new V1\Demand\Put(
											new Application\PlainRequest(),
											$this->uri,
											$this->database,
											$user
										),
										'v1/demands/{id} [DELETE]' => new V1\Demand\Delete(
											$this->database,
											$user
										),
										'v1/evolutions?page=(1 \d+)&per_page=(10 \d+) [GET]' => new V1\Evolutions\Get(
											$this->hashids,
											$this->uri,
											$this->database,
											$user,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/evolutions/{id} [DELETE]' => new V1\Evolution\Delete(
											$this->database,
											$user
										),
									];
								}
							},
							$_SERVER['REQUEST_METHOD']
						)
					),
					$uri
				),
				$uri
			),
			function(array $match) use ($uri, $hashids): Output\Template {
				/** @var \Klapuch\Application\View $destination */
				[$source, $destination] = [key($match), current($match)];
				return $destination->template(
					(new Routing\HashIdMask(
						new Routing\TypedMask(
							new Routing\CombinedMask(
								new Routing\PathMask($source, $uri),
								new Routing\QueryMask($source, $uri)
							)
						),
						['id'],
						$hashids
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
		} catch (\Throwable $ex) {
			var_dump($ex->getMessage());
			$this->logs->put(
				new Log\PrettyLog(
					$ex,
					new Log\PrettySeverity(
						new Log\JustifiedSeverity(Log\Severity::ERROR)
					)
				)
			);
			http_response_code(500);
			exit;
		}
	}
})->render();