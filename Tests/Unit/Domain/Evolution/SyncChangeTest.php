<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Evolution;

use Elasticsearch;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class SyncChangeTest extends TestCase\Runtime {
	use TestCase\Mockery;

	public function testNoIndexingOnFail(): void {
		$elasticsearch = $this->mock(Elasticsearch\Client::class);
		$origin = $this->mock(Evolution\Change::class);
		$origin->shouldReceive('revert')->once()->andThrow(\UnexpectedValueException::class, 'OK');
		$origin->shouldReceive('affect')->once()->andThrow(\UnexpectedValueException::class, 'OK');
		Assert::exception(static function () use ($origin, $elasticsearch) {
			(new Evolution\SyncChange(
				666,
				$origin,
				$elasticsearch
			))->revert();
		}, \UnexpectedValueException::class, 'OK');
		Assert::exception(static function () use ($origin, $elasticsearch) {
			(new Evolution\SyncChange(
				666,
				$origin,
				$elasticsearch
			))->affect([]);
		}, \UnexpectedValueException::class, 'OK');
	}

	public function testIndexingWithSuccess(): void {
		$elasticsearch = $this->mock(Elasticsearch\Client::class);
		$elasticsearch->shouldReceive('delete')->once()->andReturn([]);
		$elasticsearch->shouldReceive('update')->once()->andReturn([]);
		$origin = $this->mock(Evolution\Change::class);
		$origin->shouldReceive('revert')->once();
		$origin->shouldReceive('affect')->once();
		Assert::noError(static function () use ($origin, $elasticsearch) {
			(new Evolution\SyncChange(
				666,
				$origin,
				$elasticsearch
			))->revert();
		});
		Assert::noError(static function () use ($origin, $elasticsearch) {
			(new Evolution\SyncChange(
				666,
				$origin,
				$elasticsearch
			))->affect([]);
		});
	}
}

(new SyncChangeTest())->run();
