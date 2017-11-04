<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Request;
use FindMyFriends\Response;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConcurrentlyControlledResponse extends Tester\TestCase {
	use TestCase\Redis;

	public function testHeaderWithExistingETag() {
		$uri = new Uri\FakeUri(null, '/books/1');
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat()),
			$uri,
			$this->redis
		))->body();
		Assert::match(
			'"%h%"',
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), []),
				$uri,
				$this->redis
			))->headers()['ETag']
		);
	}

	public function testRestOfHeadersWithoutGeneratedETag() {
		Assert::same(
			['accept' => 'text/plain'],
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['accept' => 'text/plain']),
				new Uri\FakeUri(null, '/books/1'),
				$this->redis
			))->headers()
		);
	}

	public function testRestOfHeadersWithinGeneratedETag() {
		$uri = new Uri\FakeUri(null, '/books/1');
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat()),
			$uri,
			$this->redis
		))->body();
		Assert::count(
			2,
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['accept' => 'text/plain']),
				$uri,
				$this->redis
			))->headers()
		);
	}

	public function testPrecedenceForGeneratedETag() {
		$uri = new Uri\FakeUri(null, '/books/1');
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat()),
			$uri,
			$this->redis
		))->body();
		Assert::notSame(
			'"abc"',
			(new Response\ConcurrentlyControlledResponse(
				new Application\FakeResponse(new Output\FakeFormat(), ['ETag' => '"abc"']),
				$uri,
				$this->redis
			))->headers()['ETag']
		);
	}
}

(new ConcurrentlyControlledResponse())->run();