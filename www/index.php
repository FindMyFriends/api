<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Http;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Output;
use Klapuch\Routing;
use Klapuch\Storage;
use Klapuch\Uri;
const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini',
	LOCAL_CONFIGURATION = __DIR__ . '/../App/Configuration/.config.local.ini',
	LOGS = __DIR__ . '/../log',
	V1_ROUTES_PATH = __DIR__ . '/../App/Configuration/Routes/v1.json';

$source = new Ini\CachedSource(
	new Ini\CombinedSource(
		new Ini\ValidSource(new SplFileInfo(CONFIGURATION)),
		new Ini\ValidSource(new SplFileInfo(LOCAL_CONFIGURATION))
	)
);

$uri = new Uri\CachedUri(
	new Uri\BaseUrl(
		$_SERVER['SCRIPT_NAME'],
		$_SERVER['REQUEST_URI'],
		$_SERVER['SERVER_NAME'],
		$_SERVER['HTTPS'] ?? 'http'
	)
);

$configuration = $source->read();
echo (new Application\RawPage(
	$source,
	new Log\FilesystemLogs(
		new Log\DynamicLocation(new Log\DirectoryLocation(LOGS))
	),
	new Routing\MatchingRoutes(
		new Routing\MappedRoutes(
			new Routing\QueryRoutes(
				new Routing\PathRoutes(
					new Routing\ShortcutRoutes(
						new Routing\HttpMethodRoutes(
							new class(
								$uri,
								new Storage\SafePDO(
									$configuration['DATABASE']['dsn'],
									$configuration['DATABASE']['user'],
									$configuration['DATABASE']['password']
								)
							) implements Routing\Routes {
								private $uri;
								private $database;

								public function __construct(
									Uri\Uri $uri,
									\PDO $database
								) {
									$this->uri = $uri;
									$this->database = $database;
								}

								public function matches(): array {
									$user = (new Access\ApiEntrance(
										$this->database
									))->enter((new Application\PlainRequest())->headers());
									return [
										'v1/demands?page=(1)&per_page=(10)&sort=( ([-\s])?\w+) [GET]' => new V1\Demands\Get(
											$this->uri,
											$this->database,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/demands/{id :id} [GET]' => new V1\Demand\Get(
											$this->uri,
											$this->database,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/demands [POST]' => new V1\Demands\Post(
											new Application\PlainRequest(),
											$this->uri,
											$this->database,
											$user
										),
										'v1/demands/{id :id} [PUT]' => new V1\Demand\Put(
											new Application\PlainRequest(),
											$this->uri,
											$this->database,
											$user
										),
										'v1/demands/{id :id} [DELETE]' => new V1\Demand\Delete(
											$this->database,
											$user
										),
										'v1/evolutions?page=(1)&per_page=(10) [GET]' => new V1\Evolutions\Get(
											$this->uri,
											$this->database,
											$user,
											new Http\ChosenRole($user, ['member', 'guest'])
										),
										'v1/evolutions/{id :id} [DELETE]' => new V1\Evolution\Delete(
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
			function(array $match) use ($uri): Output\Template {
				/** @var \Klapuch\Application\View $destination */
				[$source, $destination] = [key($match), current($match)];
				return $destination->template(
					(new Routing\TypedMask(
						new Routing\CombinedMask(
							new Routing\PathMask($source, $uri),
							new Routing\QueryMask($source, $uri)
						)
					))->parameters()
				);
			}
		),
		$uri,
		$_SERVER['REQUEST_METHOD']
	)
))->render();
