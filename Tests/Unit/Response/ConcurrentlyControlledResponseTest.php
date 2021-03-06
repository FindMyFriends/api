<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Response;

use FindMyFriends\Http;
use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ConcurrentlyControlledResponseTest extends TestCase\Runtime {
	public function testHeaderWithExistingETag(): void {
		Assert::same(
			'abc',
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), []),
				new Http\FakeETag(true, 'abc')
			))->headers()['ETag']
		);
	}

	public function testRestOfHeadersWithoutGeneratedETag(): void {
		Assert::same(
			['accept' => 'text/plain'],
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['accept' => 'text/plain']),
				new Http\FakeETag(false)
			))->headers()
		);
	}

	public function testRestOfHeadersWithinGeneratedETag(): void {
		Assert::same(
			['ETag' => 'abc', 'accept' => 'text/plain'],
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['accept' => 'text/plain']),
				new Http\FakeETag(true, 'abc')
			))->headers()
		);
	}

	public function testPrecedenceForGeneratedETag(): void {
		Assert::same(
			'foo',
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['ETag' => '"abc"']),
				new Http\FakeETag(true, 'foo')
			))->headers()['ETag']
		);
	}
}

(new ConcurrentlyControlledResponseTest())->run();
