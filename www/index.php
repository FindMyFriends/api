<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Internal;
use Klapuch\Log;
use Klapuch\Output;
use Klapuch\Routing;
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
echo (new class(
	new Application\SuitedPage(
		$source,
		new Log\FilesystemLogs(
			new Log\DynamicLocation(new Log\DirectoryLocation(LOGS))
		),
		new Routing\MatchingRoutes(
			new Routing\CachedRoutes(
				new Routing\MappedRoutes(
					new Routing\CachedRoutes(
						new Routing\QueryRoutes(
							new Routing\PathRoutes(
								new Routing\CachedRoutes(
									new Routing\ShortcutRoutes(
										new Routing\HttpMethodRoutes(
											new Routing\CachedRoutes(
												new Routing\JsonRoutes(
													new SplFileInfo(
														V1_ROUTES_PATH
													)
												)
											),
											$_SERVER['REQUEST_METHOD']
										)
									)
								),
								$uri
							),
							$uri
						)
					),
					function(array $match) use ($uri): Routing\Route {
						return new Routing\TypedRoute(
							new Routing\DefaultRoute(
								key($match),
								current($match),
								$uri
							)
						);
					}
				)
			),
			$uri,
			$_SERVER['REQUEST_METHOD']
		),
		$uri
	),
	$source
) implements Output\Template {
	private $origin;
	private $config;

	public function __construct(Output\Template $origin, Ini\Source $config) {
		$this->origin = $origin;
		$this->config = $config;
	}

	public function render(array $variables = []): string {
		if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
			(new Internal\HeaderExtension($this->config->read()['HEADERS']))->improve();
			exit;
		}
		return $this->origin->render($variables);
	}
})->render();
