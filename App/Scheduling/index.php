<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

require __DIR__ . '/../../vendor/autoload.php';

use FindMyFriends;
use FindMyFriends\Configuration;
use FindMyFriends\Elasticsearch\LazyElasticsearch;
use Klapuch\Configuration\ValidIni;
use Klapuch\Log;
use Klapuch\Storage;
use Predis;

$configuration = (new Configuration\ApplicationConfiguration())->read();

$connection = new Storage\CachedConnection(
	new Storage\PDOConnection(
		new Storage\SafePDO(
			$configuration['DATABASE']['dsn'],
			$configuration['DATABASE']['user'],
			$configuration['DATABASE']['password']
		)
	),
	new Predis\Client($configuration['REDIS']['uri'])
);

$elasticsearch = new LazyElasticsearch($configuration['ELASTICSEARCH']['hosts']);

(new LoggedJob(
	new SelectedJob(
		$argv[1],
		new MarkedJob(new Task\Cron($connection), $connection),
		new MarkedJob(new Task\RefreshMaterializedView($connection), $connection),
		new MarkedJob(new Task\GenerateJsonSchema($connection), $connection),
		new MarkedJob(new Task\ElasticsearchReindex($elasticsearch->create()), $connection),
		new FindMyFriends\Scheduling\Task\CheckChangedConfiguration(
			new \SplFileInfo(__DIR__ . '/../../docker/nginx'),
			new SerialJobs(
				new Task\GenerateNginxRoutes(
					new ValidIni(new \SplFileInfo(__DIR__ . '/../Configuration/.routes.ini')),
					new \SplFileInfo(__DIR__ . '/../../docker/nginx/routes.conf')
				),
				new FindMyFriends\Scheduling\Task\GenerateNginxConfiguration(
					new \SplFileInfo(__DIR__ . '/../../docker/nginx/preflight.conf')
				)
			)
		),
		new MarkedJob(
			new Task\GenerateNginxRoutes(
				new ValidIni(new \SplFileInfo(__DIR__ . '/../Configuration/.routes.ini')),
				new \SplFileInfo(__DIR__ . '/../../docker/nginx/routes.conf')
			),
			$connection
		),
		new MarkedJob(
			new FindMyFriends\Scheduling\Task\GenerateNginxConfiguration(
				new \SplFileInfo(__DIR__ . '/../../docker/nginx/preflight.conf')
			),
			$connection
		)
	),
	new Log\ChainedLogs(
		new FindMyFriends\Log\FilesystemLogs(
			new Log\DynamicLocation($configuration['LOGS']['directory'])
		),
		new FindMyFriends\Log\ElasticsearchLogs($elasticsearch)
	)
))->fulfill();
