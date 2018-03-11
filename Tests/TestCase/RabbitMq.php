<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use Klapuch\Configuration;
use PhpAmqpLib;

trait RabbitMq {
	/** @var \PhpAmqpLib\Connection\AMQPLazyConnection */
	protected $rabbitMq;

	protected function setUp(): void {
		parent::setUp();
		$credentials = (new Configuration\ValidIni(
			new \SplFileInfo(__DIR__ . '/../Configuration/.secrets.ini')
		))->read();
		$this->rabbitMq = new PhpAmqpLib\Connection\AMQPLazyConnection(
			$credentials['RABBITMQ']['host'],
			$credentials['RABBITMQ']['port'],
			$credentials['RABBITMQ']['user'],
			$credentials['RABBITMQ']['pass'],
			$credentials['RABBITMQ']['vhost']
		);
	}
}