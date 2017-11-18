<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConcurrentlyCreatedResponseTest extends Tester\TestCase {
	use TestCase\Redis;

	public function testCreatingHexaETag() {
		$uri = new Uri\FakeUri('http://localhost', '/books/1');
		(new Response\ConcurrentlyCreatedResponse(
			new Application\FakeResponse(new Output\FakeFormat(), []),
			$this->redis,
			$uri
		))->headers();
		Assert::match('"%h%"', $this->redis->get($uri->path()));
	}

	public function testPrependLocationHeader() {
		Assert::same(
			['Location' => 'http://localhost', 'Accept' => 'text/html'],
			(new Response\ConcurrentlyCreatedResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['Accept' => 'text/html']),
				$this->redis,
				new Uri\FakeUri('http://localhost', '/books/1')
			))->headers()
		);
	}

	public function test201CreatedStatusCode() {
		Assert::same(
			HTTP_CREATED,
			(new Response\ConcurrentlyCreatedResponse(
				new Application\FakeResponse(),
				$this->redis,
				new Uri\FakeUri()
			))->status()
		);
	}

	public function testMultipleSameObjectsWithSameETag() {
		$uri = new Uri\FakeUri('http://localhost', '/books/1');
		(new Response\ConcurrentlyCreatedResponse(
			new Application\FakeResponse(new Output\FakeFormat(), []),
			$this->redis,
			$uri
		))->headers();
		$first = $this->redis->get($uri->path());
		(new Response\ConcurrentlyCreatedResponse(
			new Application\FakeResponse(new Output\FakeFormat(), []),
			$this->redis,
			$uri
		))->headers();
		Assert::same($first, $this->redis->get($uri->path()));
	}

	public function testMultipleDifferentObjectsWithDifferentETag() {
		$uri = new Uri\FakeUri('http://localhost', '/books/1');
		(new Response\ConcurrentlyCreatedResponse(
			new Application\FakeResponse(new Output\Json(), []),
			$this->redis,
			$uri
		))->headers();
		$first = $this->redis->get($uri->path());
		(new Response\ConcurrentlyCreatedResponse(
			new Application\FakeResponse(new Output\FakeFormat(), []),
			$this->redis,
			$uri
		))->headers();
		Assert::notSame($first, $this->redis->get($uri->path()));
	}
}

(new ConcurrentlyCreatedResponseTest())->run();