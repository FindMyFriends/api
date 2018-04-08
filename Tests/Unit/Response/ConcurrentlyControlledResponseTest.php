<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Http;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConcurrentlyControlledResponseTest extends Tester\TestCase {
	public function testHeaderWithExistingETag() {
		Assert::same(
			'abc',
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), []),
				new Http\FakeETag(true, 'abc')
			))->headers()['ETag']
		);
	}

	public function testRestOfHeadersWithoutGeneratedETag() {
		Assert::same(
			['accept' => 'text/plain'],
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['accept' => 'text/plain']),
				new Http\FakeETag(false)
			))->headers()
		);
	}

	public function testRestOfHeadersWithinGeneratedETag() {
		Assert::same(
			['ETag' => 'abc', 'accept' => 'text/plain'],
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['accept' => 'text/plain']),
				new Http\FakeETag(true, 'abc')
			))->headers()
		);
	}

	public function testPrecedenceForGeneratedETag() {
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
