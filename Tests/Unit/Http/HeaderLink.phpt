<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Http;

use FindMyFriends\Http;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class HeaderLink extends \Tester\TestCase {
	public function testAddingSingleDirection() {
		Assert::same(
			'<https://localhost/abc?page=1>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', []),
				['first' => 1]
			))->serialization()
		);
	}

	public function testAppendingPathWithoutDoubledSlashes() {
		Assert::same(
			'<https://localhost/abc/?page=1>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost/', '/abc/', []),
				['first' => 1]
			))->serialization()
		);
	}

	public function testAddingMultipleDirections() {
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

	public function testMergingWithCurrentDirections() {
		Assert::same(
			'<https://localhost/abc?page=1&per_page=20>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', ['per_page' => 20]),
				['first' => 1]
			))->serialization()
		);
	}

	public function testPaginationPageWithPrecedence() {
		Assert::same(
			'<https://localhost/abc?page=1&per_page=20>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', ['per_page' => 20, 'page' => 'xx']),
				['first' => 1]
			))->serialization()
		);
	}

	public function testUsingRFC3986() {
		Assert::same(
			'<https://localhost/abc?page=1&foo%20bar=bar%20foo>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', ['foo bar' => 'bar foo']),
				['first' => 1]
			))->serialization()
		);
	}

	public function testDynamicAddingWithDynamicPrecedence() {
		Assert::same(
			'<https://localhost/abc?page=2>; rel="first", <https://localhost/abc?page=4>; rel="last"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', []),
				['first' => 1]
			))->with('last', 4)->with('first', 2)->serialization()
		);
	}

	public function testAdjusting() {
		Assert::same(
			'<https://localhost/abc?page=2>; rel="first"',
			(new Http\HeaderLink(
				new Uri\FakeUri('https://localhost', '/abc', []),
				['first' => 1]
			))->adjusted(
				'first',
				function(int $page): int {
					return $page + 1;
				}
			)->serialization()
		);
	}
}

(new HeaderLink())->run();