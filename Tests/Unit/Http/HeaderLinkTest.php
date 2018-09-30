<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Http;

use FindMyFriends\Http;
use FindMyFriends\TestCase;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class HeaderLinkTest extends TestCase\Runtime {
	public function testAddingSingleDirection(): void {
		Assert::same(
			'<https://localhost/abc?page=1>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', []),
				['first' => 1]
			))->serialization()
		);
	}

	public function testAppendingPathWithoutDoubledSlashes(): void {
		Assert::same(
			'<https://localhost/abc/?page=1>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost/', '/abc/', []),
				['first' => 1]
			))->serialization()
		);
	}

	public function testAddingMultipleDirections(): void {
		Assert::same(
			'<https://localhost/abc?page=1>; rel="first", <https://localhost/abc?page=4>; rel="last"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', []),
				[
					'first' => 1,
					'last' => 4,
				]
			))->serialization()
		);
	}

	public function testMergingWithCurrentDirections(): void {
		Assert::same(
			'<https://localhost/abc?page=1&per_page=20>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', ['per_page' => 20]),
				['first' => 1]
			))->serialization()
		);
	}

	public function testPaginationPageWithPrecedence(): void {
		Assert::same(
			'<https://localhost/abc?page=1&per_page=20>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', ['per_page' => 20, 'page' => 'xx']),
				['first' => 1]
			))->serialization()
		);
	}

	public function testUsingRFC3986(): void {
		Assert::same(
			'<https://localhost/abc?page=1&foo%20bar=bar%20foo>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', ['foo bar' => 'bar foo']),
				['first' => 1]
			))->serialization()
		);
	}

	public function testDynamicAddingWithDynamicPrecedence(): void {
		Assert::same(
			'<https://localhost/abc?page=2>; rel="first", <https://localhost/abc?page=4>; rel="last"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', []),
				['first' => 1]
			))->with('last', 4)->with('first', 2)->serialization()
		);
	}

	public function testAdjusting(): void {
		Assert::same(
			'<https://localhost/abc?page=2>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', []),
				['first' => 1]
			))->adjusted(
				'first',
				static function(int $page): int {
					return $page + 1;
				}
			)->serialization()
		);
	}
}

(new HeaderLinkTest())->run();
