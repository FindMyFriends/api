<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

require __DIR__ . '/../../vendor/autoload.php';

use Elasticsearch;
use FindMyFriends;
use FindMyFriends\Configuration;
use Klapuch\Configuration\ValidIni;
use Klapuch\Log;
use Klapuch\Storage;
use Predis;

$configuration = (new Configuration\ApplicationConfiguration())->read();

$database = new Storage\MetaPDO(
	new Storage\SideCachedPDO(
		new Storage\SafePDO(
			$configuration['DATABASE']['dsn'],
			$configuration['DATABASE']['user'],
			$configuration['DATABASE']['password']
		)
	),
	new Predis\Client($configuration['REDIS']['uri'])
);

$elasticsearch = Elasticsearch\ClientBuilder::create()
	->setHosts($configuration['ELASTICSEARCH']['hosts'])
	->build();

(new LoggedJob(
	new SelectedJob(
		$argv[1],
		new MarkedJob(new Task\Cron($database), $database),
		new MarkedJob(new Task\RefreshMaterializedView($database), $database),
		new MarkedJob(new Task\GenerateJsonSchema($database), $database),
		new MarkedJob(new Task\ElasticsearchReindex($elasticsearch), $database),
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
			$database
		),
		new MarkedJob(
			new FindMyFriends\Scheduling\Task\GenerateNginxConfiguration(
				new \SplFileInfo(__DIR__ . '/../../docker/nginx/preflight.conf')
			),
			$database
		)
	),
	new Log\ChainedLogs(
		new FindMyFriends\Log\FilesystemLogs(
			new Log\DynamicLocation($configuration['LOGS']['directory'])
		),
		new FindMyFriends\Log\ElasticsearchLogs($elasticsearch)
	)
))->fulfill();
