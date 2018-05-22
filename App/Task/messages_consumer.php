<?php
declare(strict_types = 1);

require __DIR__ . '/../../vendor/autoload.php';

use FindMyFriends\Configuration;
use FindMyFriends\Domain\Access;
use Klapuch\Log;
use Klapuch\Storage;

$configuration = (new Configuration\ApplicationConfiguration())->read();

(new Access\Consumer(
	new PhpAmqpLib\Connection\AMQPStreamConnection(
		$configuration['RABBITMQ']['host'],
		$configuration['RABBITMQ']['port'],
		$configuration['RABBITMQ']['user'],
		$configuration['RABBITMQ']['pass'],
		$configuration['RABBITMQ']['vhost']
	),
	new Log\ChainedLogs(
		new Log\FilesystemLogs(new Log\DynamicLocation(sprintf('%s/../../%s', __DIR__, $configuration['LOGS']['directory']))),
		new Log\FilesystemLogs(new SplFileInfo(sprintf('%s/../../%s', __DIR__, $configuration['LOGS']['file'])))
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
	)
))->consume();
