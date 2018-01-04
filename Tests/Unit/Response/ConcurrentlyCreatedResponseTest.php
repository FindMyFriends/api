<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Http;
use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConcurrentlyCreatedResponseTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testStoredETag() {
		$eTag = $this->mock(Http\ETag::class);
		$eTag->shouldReceive('set')->once();
		Assert::same(
			['Location' => 'http://localhost', 'Accept' => 'text/html'],
			(new Response\ConcurrentlyCreatedResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['Accept' => 'text/html']),
				$eTag,
				new Uri\FakeUri('http://localhost', '/books/1')
			))->headers()
		);
	}

	public function testPrependLocationHeader() {
		Assert::same(
			['Location' => 'http://localhost', 'Accept' => 'text/html'],
			(new Response\ConcurrentlyCreatedResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['Accept' => 'text/html']),
				new Http\FakeETag(),
				new Uri\FakeUri('http://localhost', '/books/1')
			))->headers()
		);
	}

	public function test201CreatedStatusCode() {
		Assert::same(
			HTTP_CREATED,
			(new Response\ConcurrentlyCreatedResponse(
				new Application\FakeResponse(),
				new Http\FakeETag(),
				new Uri\FakeUri()
			))->status()
		);
	}
}

(new ConcurrentlyCreatedResponseTest())->run();