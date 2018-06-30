<?php
declare(strict_types = 1);

require __DIR__ . '/../../vendor/autoload.php';

use FindMyFriends\Configuration;
use FindMyFriends\Domain\Search;
use Klapuch\Log;
use Klapuch\Storage;

$configuration = (new Configuration\ApplicationConfiguration())->read();

$elasticsearch = Elasticsearch\ClientBuilder::create()
	->setHosts($configuration['ELASTICSEARCH']['hosts'])
	->build();

(new Search\Consumer(
	new PhpAmqpLib\Connection\AMQPStreamConnection(
		$configuration['RABBITMQ']['host'],
		$configuration['RABBITMQ']['port'],
		$configuration['RABBITMQ']['user'],
		$configuration['RABBITMQ']['pass'],
		$configuration['RABBITMQ']['vhost']
	),
	new Log\ChainedLogs(
		new FindMyFriends\Log\FilesystemLogs(
			new Log\DynamicLocation($configuration['LOGS']['directory'])
		),
		new FindMyFriends\Log\ElasticsearchLogs($elasticsearch)
	),
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
	Elasticsearch\ClientBuilder::create()
		->setHosts($configuration['ELASTICSEARCH']['hosts'])
		->build()
))->consume();
