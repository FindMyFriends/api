<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Domain\Evolution;

use Elasticsearch;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class SyncChainTest extends Tester\TestCase {
	use TestCase\Mockery;

	/**
	 * @throws \UnexpectedValueException OK
	 */
	public function testNoIndexingOnFail() {
		$elasticsearch = $this->mock(Elasticsearch\Client::class);
		$origin = $this->mock(Evolution\Chain::class);
		$origin->shouldReceive('extend')->once()->andThrow(\UnexpectedValueException::class, 'OK');
		(new Evolution\SyncChain(
			$origin,
			$elasticsearch
		))->extend([]);
	}

	public function testIndexingWithSuccess() {
		$elasticsearch = $this->mock(Elasticsearch\Client::class);
		$elasticsearch->shouldReceive('index')->once()->andReturn([]);
		$origin = $this->mock(Evolution\Chain::class);
		$origin->shouldReceive('extend')->once()->andReturn(10);
		Assert::noError(static function () use ($origin, $elasticsearch) {
			(new Evolution\SyncChain(
				$origin,
				$elasticsearch
			))->extend([]);
		});
	}
}

(new SyncChainTest())->run();
