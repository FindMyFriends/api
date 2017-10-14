<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Response;

use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\UI;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PaginatedResponse extends \Tester\TestCase {
	public function testPriorityToNewHeader() {
		Assert::notSame(
			['Link' => 'xxx'],
			(new Response\PaginatedResponse(
				new Application\FakeResponse(null, ['Link' => 'xxx']),
				10,
				new UI\FakePagination([]),
				new Uri\FakeUri()
			))->headers()
		);
	}

	public function testAddingOtherHeaders() {
		Assert::contains(
			'text/html',
			(new Response\PaginatedResponse(
				new Application\FakeResponse(null, ['Accept' => 'text/html']),
				10,
				new UI\FakePagination([]),
				new Uri\FakeUri()
			))->headers()
		);
	}

	public function testPartialResponseForNotLastPage() {
		Assert::same(
			206,
			(new Response\PaginatedResponse(
				new class implements Application\Response {
					public function body(): Output\Format {
					}

					public function headers(): array {
						return [];
					}

					public function status(): int {
						return 301;
					}
				},
				5,
				new UI\FakePagination([1, 9]),
				new Uri\FakeUri()
			))->status()
		);
	}

	public function testDelegatedStatusCodeForLastPage() {
		Assert::same(
			201,
			(new Response\PaginatedResponse(
				new class implements Application\Response {
					public function body(): Output\Format {
					}

					public function headers(): array {
						return [];
					}

					public function status(): int {
						return 201;
					}
				},
				10,
				new UI\FakePagination([1, 10]),
				new Uri\FakeUri()
			))->status()
		);
	}

	public function testDelegatedStatusCodeForOversteppingLastPage() {
		Assert::same(
			204,
			(new Response\PaginatedResponse(
				new class implements Application\Response {
					public function body(): Output\Format {
					}

					public function headers(): array {
						return [];
					}

					public function status(): int {
						return 204;
					}
				},
				20,
				new UI\FakePagination([1, 10]),
				new Uri\FakeUri()
			))->status()
		);
	}
}

(new PaginatedResponse())->run();