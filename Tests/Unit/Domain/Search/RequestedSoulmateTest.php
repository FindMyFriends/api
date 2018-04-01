<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class RequestedSoulmateTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testProcessingWithSuccess() {
		$id = 1;
		$requests = $this->mock(Search\Requests::class);
		$requests->shouldReceive('refresh')->once()->with($id, 'processing');
		$requests->shouldReceive('refresh')->once()->with($id, 'succeed');
		$origin = $this->mock(Search\Soulmates::class);
		$origin->shouldReceive('find')->once();
		Assert::noError(function () use ($id, $requests, $origin) {
			(new Search\RequestedSoulmates(
				$requests,
				$origin
			))->find($id);
		});
	}

	public function testRethrowingOnFail() {
		$id = 1;
		$requests = $this->mock(Search\Requests::class);
		$requests->shouldReceive('refresh')->once()->with($id, 'processing');
		$requests->shouldReceive('refresh')->once()->with($id, 'failed');
		$origin = $this->mock(Search\Soulmates::class);
		$origin->shouldReceive('find')->once()->andThrow(new \DomainException('foo'));
		Assert::exception(function () use ($id, $requests, $origin) {
			(new Search\RequestedSoulmates(
				$requests,
				$origin
			))->find($id);
		}, \DomainException::class, 'foo');
	}
}

(new RequestedSoulmateTest())->run();