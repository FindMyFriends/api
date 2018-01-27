<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Configuration\CreatedHashids;
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
	SECRET_CONFIGURATION = __DIR__ . '/../App/Configuration/.secrets.ini',
	HASHIDS_CONFIGURATION = __DIR__ . '/../App/Configuration/.hashids.json',
	HASHIDS_SECRET_CONFIGURATION = __DIR__ . '/../App/Configuration/.hashids.secret.json',
	LOGS = __DIR__ . '/../log';

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
								$configuration['HASHIDS']
							) implements Routing\Routes {
								private $uri;
								private $database;
								private $hashids;

								public function __construct(Uri\Uri $uri, \PDO $database, array $hashids) {
									$this->uri = $uri;
									$this->database = $database;
									$this->hashids = $hashids;
								}

								public function matches(): array {
									$user = (new Access\ApiEntrance(
										$this->database
									))->enter((new Application\PlainRequest())->headers());
									return [
										'v1/demands [OPTIONS]' => new V1\Demands\Options($this->database),
										'v1/demands?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?\w+) [GET]' => new V1\Demands\Get(
											$this->hashids['demand']['hashid'],
											$this->uri,
											$this->database,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/demands/{id} [GET]' => new V1\Demand\Get(
											$this->hashids['demand']['hashid'],
											$this->uri,
											$this->database,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/demands [POST]' => new V1\Demands\Post(
											$this->hashids['demand']['hashid'],
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
											$this->hashids['evolution']['hashid'],
											$this->uri,
											$this->database,
											$user,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/evolutions/{id} [DELETE]' => new V1\Evolution\Delete(
											$this->database,
											$user
										),
										'v1/.+ [OPTIONS]' => new V1\Options()
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
			var_dump($ex->getMessage());
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