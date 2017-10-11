<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Klapuch\Application;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ResponseWithRange extends \Tester\TestCase {
	public function testStartFromZero() {
		Assert::same(
			['Content-Range' => 'books 0-20/100'],
			(new Response\ResponseWithRange(
				new Application\FakeResponse(null, []),
				'books',
				1,
				20,
				100
			))->headers()
		);
	}

	public function testCountedOffset() {
		Assert::same(
			['Content-Range' => 'books 20-40/100'],
			(new Response\ResponseWithRange(
				new Application\FakeResponse(null, []),
				'books',
				2,
				20,
				100
			))->headers()
		);
	}

	/**
	 * @throws \UnexpectedValueException Page out of allowed range
	 */
	public function testThrowingOnOutOfRange() {
		(new Response\ResponseWithRange(
			new Application\FakeResponse(null, []),
			'books',
			10,
			20,
			100
		))->headers();
	}

	public function testPassingWithPageOnEdge() {
		Assert::noError(function() {
			(new Response\ResponseWithRange(
				new Application\FakeResponse(null, []),
				'books',
				5,
				20,
				100
			))->headers();
		});
	}

	public function testDefaultSizeForPerPage() {
		Assert::same(
			['Content-Range' => 'books 0-5/6'],
			(new Response\ResponseWithRange(
				new Application\FakeResponse(null, []),
				'books',
				1,
				80,
				6
			))->headers()
		);
	}

	public function testPrecedenceToCreatedHeader() {
		Assert::same(
			['Content-Range' => 'books 0-20/100'],
			(new Response\ResponseWithRange(
				new Application\FakeResponse(null, ['Content-Range' => 'foo']),
				'books',
				1,
				20,
				100
			))->headers()
		);
	}

	public function testIncludingOtherHeaders() {
		Assert::same(
			['Content-Range' => 'books 0-20/100', 'foo' => 'bar'],
			(new Response\ResponseWithRange(
				new Application\FakeResponse(null, ['foo' => 'bar']),
				'books',
				1,
				20,
				100
			))->headers()
		);
	}
}

(new ResponseWithRange())->run();