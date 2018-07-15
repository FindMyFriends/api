<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling;

require __DIR__ . '/../../vendor/autoload.php';

use Elasticsearch;
use FindMyFriends;
use FindMyFriends\Configuration;
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
	new SerialJobs(
		new RepeatedJob(
			new MarkedJob(
				new RefreshMaterializedViewJob('prioritized_evolution_fields', $database),
				$database
			),
			'PT10M',
			$database
		),
		new MarkedJob(new JsonSchemaJob($database), $database),
		new MarkedJob(new ElasticsearchReindexJob($elasticsearch), $database)
	),
	new Log\ChainedLogs(
		new FindMyFriends\Log\FilesystemLogs(
			new Log\DynamicLocation($configuration['LOGS']['directory'])
		),
		new FindMyFriends\Log\ElasticsearchLogs($elasticsearch)
	)
))->fulfill();
