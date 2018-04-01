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
		$demand = 1;
		$self = 2;
		$requests = $this->mock(Search\Requests::class);
		$requests->shouldReceive('refresh')->once()->with($demand, 'processing', $self);
		$requests->shouldReceive('refresh')->once()->with($demand, 'succeed', $self);
		$origin = $this->mock(Search\Soulmates::class);
		$origin->shouldReceive('find')->once();
		Assert::noError(function () use ($demand, $requests, $origin, $self) {
			(new Search\RequestedSoulmates(
				$self,
				$requests,
				$origin
			))->find($demand);
		});
	}

	public function testRethrowingOnFail() {
		$demand = 1;
		$self = 2;
		$requests = $this->mock(Search\Requests::class);
		$requests->shouldReceive('refresh')->once()->with($demand, 'processing', $self);
		$requests->shouldReceive('refresh')->once()->with($demand, 'failed', $self);
		$origin = $this->mock(Search\Soulmates::class);
		$origin->shouldReceive('find')->once()->andThrow(new \DomainException('foo'));
		Assert::exception(function () use ($demand, $requests, $origin, $self) {
			(new Search\RequestedSoulmates(
				$self,
				$requests,
				$origin
			))->find($demand);
		}, \DomainException::class, 'foo');
	}
}

(new RequestedSoulmateTest())->run();