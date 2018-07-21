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
		new MarkedJob(
			new GroupedJob(
				'cron',
				new SerialJobs(
					new RepeatedJob(
						new MarkedJob(
							new Task\RefreshMaterializedView('prioritized_evolution_fields', $database),
							$database
						),
						'PT10M',
						$database
					)
				)
			),
			$database
		),
		new MarkedJob(
			new Task\RefreshMaterializedView('prioritized_evolution_fields', $database),
			$database
		),
		new MarkedJob(new Task\GenerateJsonSchema($database), $database),
		new MarkedJob(new Task\ElasticsearchReindex($elasticsearch), $database),
		new MarkedJob(
			new Task\GenerateNginxRoutes(
				new ValidIni(new \SplFileInfo(__DIR__ . '/../Configuration/.routes.ini')),
				new \SplFileInfo(__DIR__ . '/../../docker/nginx/routes.conf')
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
