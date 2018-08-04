<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use Elasticsearch;
use Hashids\Hashids;
use Klapuch\Encryption;
use Klapuch\Routing;
use Klapuch\Storage;
use Klapuch\Uri;
use PhpAmqpLib;
use Predis;

/**
 * Simplified application routes for testing
 */
final class TestApplicationRoutes implements Routing\Routes {
	public function matches(): array {
		return (new ApplicationRoutes(
			new Uri\FakeUri(),
			new class extends Storage\MetaPDO {
				public function __construct() {
				}
			},
			new class implements Predis\ClientInterface {
				public function getProfile(): void {
				}

				public function getOptions(): void {
				}

				public function connect(): void {
				}

				public function disconnect(): void {
				}

				public function getConnection(): void {
				}

				/**
				 * @param string $method
				 * @param array $arguments
				 */
				public function createCommand($method, $arguments = []): void {
				}

				public function executeCommand(Predis\Command\CommandInterface $command): void {
				}

				/**
				 * @param string $method
				 * @param array $arguments
				 */
				public function __call($method, $arguments): void {
				}

			},
			Elasticsearch\ClientBuilder::create()->build(),
			new PhpAmqpLib\Connection\AMQPLazyConnection(
				'',
				'',
				'',
				''
			),
			new Encryption\FakeCipher(),
			[
				'spot' => new Hashids(),
				'demand' => new Hashids(),
				'evolution' => new Hashids(),
				'soulmate' => new Hashids(),
			]
		))->matches();
	}
}
