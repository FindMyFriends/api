<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Request;

use FindMyFriends\Http;
use FindMyFriends\Request;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConcurrentlyControlledRequestTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testCreatingFirstETag() {
		$eTag = $this->mock(Http\ETag::class);
		$eTag->shouldReceive('exists')->andReturn(false)->once();
		$eTag->shouldReceive('set')->once();
		Assert::noError(function() use ($eTag) {
			(new Request\ConcurrentlyControlledRequest(
				new Application\FakeRequest(new Output\FakeFormat(), []),
				$eTag
			))->body();
		});
	}

	public function testThrowingOnSecondSameRequestsWithoutETag() {
		Assert::exception(function() {
			(new Request\ConcurrentlyControlledRequest(
				new Application\FakeRequest(new Output\FakeFormat(), []),
				new Http\FakeETag(true, '')
			))->body();
		}, \UnexpectedValueException::class, 'ETag does not match your preferences', HTTP_PRECONDITION_FAILED);
	}

	public function testThrowingOnNotMatchingETag() {
		Assert::exception(function() {
			(new Request\ConcurrentlyControlledRequest(
				new Application\FakeRequest(new Output\FakeFormat(), ['If-Match' => '"abc"']),
				new Http\FakeETag(true, '"foo"')
			))->body();
		}, \UnexpectedValueException::class, 'ETag does not match your preferences', HTTP_PRECONDITION_FAILED);
	}

	public function testPassingOnNotMatchingETagForInvertedHeader() {
		Assert::noError(function() {
			(new Request\ConcurrentlyControlledRequest(
				new Application\FakeRequest(new Output\FakeFormat(), ['If-None-Match' => '"abc"']),
				new Http\FakeETag(true, '"foo"')
			))->body();
		});
	}
}

(new ConcurrentlyControlledRequestTest())->run();