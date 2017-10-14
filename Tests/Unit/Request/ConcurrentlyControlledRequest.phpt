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
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ConcurrentlyControlledRequest extends \Tester\TestCase {
	use TestCase\Redis;

	public function testCreatingFirstETag() {
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat(), []),
			new Uri\FakeUri(null, '/books/1'),
			new Http\ETagRedis($this->redis)
		))->body();
		Assert::match('"%h%"', (new Http\ETagRedis($this->redis))->get('/books/1'));
	}

	public function testThrowingOnSecondSameRequestsWithoutETag() {
		$request = new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat(), []),
			new Uri\FakeUri(null, '/books/1'),
			$this->redis
		);
		$request->body();
		Assert::exception(function() use ($request) {
			$request->body();
		}, \UnexpectedValueException::class, 'ETag does not match your preferences');
	}

	public function testThrowingOnNotMatchingETag() {
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat(), []),
			new Uri\FakeUri(null, '/books/1'),
			$this->redis
		))->body();
		Assert::exception(function() {
			(new Request\ConcurrentlyControlledRequest(
				new Application\FakeRequest(new Output\FakeFormat(), ['If-Match' => '"abc"']),
				new Uri\FakeUri(null, '/books/1'),
				$this->redis
			))->body();
		}, \UnexpectedValueException::class, 'ETag does not match your preferences');
	}

	public function testPassingOnNotMatchingETagForInvertedHeader() {
		Assert::noError(function() {
			(new Request\ConcurrentlyControlledRequest(
				new Application\FakeRequest(new Output\FakeFormat(), []),
				new Uri\FakeUri(null, '/books/1'),
				$this->redis
			))->body();
			(new Request\ConcurrentlyControlledRequest(
				new Application\FakeRequest(new Output\FakeFormat(), ['If-None-Match' => '"abc"']),
				new Uri\FakeUri(null, '/books/1'),
				$this->redis
			))->body();
		});
	}

	public function testSameRequestWithSameETag() {
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat(), ['whatever']),
			new Uri\FakeUri(null, '/books/1'),
			$this->redis
		))->body();
		$eTag = $this->redis->get('_ETAG:/books/1');
		$this->redis->flushall();
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat(), []),
			new Uri\FakeUri(null, '/books/1'),
			$this->redis
		))->body();
		Assert::same($this->redis->get('_ETAG:/books/1'), $eTag);
	}

	public function testRewritingOldETag() {
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\FakeFormat(), []),
			new Uri\FakeUri(null, '/books/1'),
			new Http\ETagRedis($this->redis)
		))->body();
		$eTag = (new Http\ETagRedis($this->redis))->get('/books/1');
		(new Request\ConcurrentlyControlledRequest(
			new Application\FakeRequest(new Output\Json(['a' => 'b']), ['If-Match' => $eTag]),
			new Uri\FakeUri(null, '/books/1'),
			new Http\ETagRedis($this->redis)
		))->body();
		Assert::notSame((new Http\ETagRedis($this->redis))->get('/books/1'), $eTag);
	}

	public function testAllowingAnonymousClasses() {
		Assert::noError(function() {
			(new Request\ConcurrentlyControlledRequest(
				new Application\FakeRequest(new class implements Output\Format {
					public function with($tag, $content = null): Output\Format {
					}

					public function serialization(): string {
						return '';
					}

					public function adjusted($tag, callable $adjustment): Output\Format {
					}
				}, []),
				new Uri\FakeUri(null, '/books/1'),
				$this->redis
			))->body();
		});
	}
}

(new ConcurrentlyControlledRequest())->run();