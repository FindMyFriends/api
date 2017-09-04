<?php
declare(strict_types = 1);
require __DIR__ . '/../vendor/autoload.php';

use Klapuch\Application;
use Klapuch\Ini;
use Klapuch\Log;
use Klapuch\Routing;
use Klapuch\Uri;

const CONFIGURATION = __DIR__ . '/../App/Configuration/.config.ini',
	LOCAL_CONFIGURATION = __DIR__ . '/../App/Configuration/.config.local.ini',
	LOGS = __DIR__ . '/../log',
	V1_ROUTES_PATH = __DIR__ . '/../App/Configuration/Routes/v1.json';
echo (new Application\SuitedPage(
	new Ini\CombinedSource(
		new Ini\ValidSource(CONFIGURATION, new Ini\TypedSource(CONFIGURATION)),
		new Ini\MutedSource(
			new Ini\ValidSource(
				LOCAL_CONFIGURATION,
				new Ini\TypedSource(LOCAL_CONFIGURATION)
			)
		)
	),
	new Log\FilesystemLogs(new Log\DynamicLocation(new Log\DirectoryLocation(LOGS))),
	new Routing\HttpRoutes(
		json_decode(file_get_contents(V1_ROUTES_PATH), true),
		$_SERVER['REQUEST_METHOD']
	),
	new Uri\CachedUri(
		new Uri\BaseUrl(
			$_SERVER['SCRIPT_NAME'],
			$_SERVER['REQUEST_URI'],
			$_SERVER['SERVER_NAME'],
			$_SERVER['HTTPS'] ?? 'http'
		)
	)
))->render();