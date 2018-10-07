<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class RequestedSoulmateTest extends TestCase\Runtime {
	use TestCase\Mockery;

	public function testProcessingWithSuccess(): void {
		$self = 2;
		$requests = $this->mock(Search\Requests::class);
		$requests->shouldReceive('refresh')->once()->with('processing', $self);
		$requests->shouldReceive('refresh')->once()->with('succeed', $self);
		$origin = $this->mock(Search\Soulmates::class);
		$origin->shouldReceive('matches')->once();
		Assert::noError(static function () use ($requests, $origin, $self) {
			(new Search\RequestedSoulmates(
				$self,
				$requests,
				$origin
			))->matches(new Dataset\EmptySelection());
		});
	}

	public function testRethrowingOnFail(): void {
		$self = 2;
		$requests = $this->mock(Search\Requests::class);
		$requests->shouldReceive('refresh')->once()->with('processing', $self);
		$requests->shouldReceive('refresh')->once()->with('failed', $self);
		$origin = $this->mock(Search\Soulmates::class);
		$origin->shouldReceive('matches')->once()->andThrow(new \DomainException('foo'));
		Assert::exception(static function () use ($requests, $origin, $self) {
			(new Search\RequestedSoulmates(
				$self,
				$requests,
				$origin
			))->matches(new Dataset\EmptySelection());
		}, \DomainException::class, 'foo');
	}
}

(new RequestedSoulmateTest())->run();
