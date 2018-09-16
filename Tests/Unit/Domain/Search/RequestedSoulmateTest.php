<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class RequestedSoulmateTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testProcessingWithSuccess() {
		$self = 2;
		$requests = $this->mock(Search\Requests::class);
		$requests->shouldReceive('refresh')->once()->with('processing', $self);
		$requests->shouldReceive('refresh')->once()->with('succeed', $self);
		$origin = $this->mock(Search\Soulmates::class);
		$origin->shouldReceive('seek')->once();
		Assert::noError(static function () use ($requests, $origin, $self) {
			(new Search\RequestedSoulmates(
				$self,
				$requests,
				$origin
			))->seek();
		});
	}

	public function testRethrowingOnFail() {
		$self = 2;
		$requests = $this->mock(Search\Requests::class);
		$requests->shouldReceive('refresh')->once()->with('processing', $self);
		$requests->shouldReceive('refresh')->once()->with('failed', $self);
		$origin = $this->mock(Search\Soulmates::class);
		$origin->shouldReceive('seek')->once()->andThrow(new \DomainException('foo'));
		Assert::exception(static function () use ($requests, $origin, $self) {
			(new Search\RequestedSoulmates(
				$self,
				$requests,
				$origin
			))->seek();
		}, \DomainException::class, 'foo');
	}
}

(new RequestedSoulmateTest())->run();
