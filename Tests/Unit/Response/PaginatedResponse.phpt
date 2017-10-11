<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
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
		(new Response\PaginatedResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
				}

				public function headers(): array {
					http_response_code(301);
					return [];
				}
			},
			5,
			new UI\FakePagination([1, 9]),
			new Uri\FakeUri()
		))->headers();
		Assert::same(206, http_response_code());
	}

	public function testOkResponseForLastPage() {
		(new Response\PaginatedResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
				}

				public function headers(): array {
					http_response_code(301);
					return ['Accept' => 'text/html'];
				}
			},
			10,
			new UI\FakePagination([1, 10]),
			new Uri\FakeUri()
		))->headers();
		Assert::same(200, http_response_code());
	}

	public function testOkResponseForOversteppingLastPage() {
		(new Response\PaginatedResponse(
			new class implements Application\Response {
				public function body(): Output\Format {
				}

				public function headers(): array {
					http_response_code(301);
					return ['Accept' => 'text/html'];
				}
			},
			20,
			new UI\FakePagination([1, 10]),
			new Uri\FakeUri()
		))->headers();
		Assert::same(200, http_response_code());
	}
}

(new PaginatedResponse())->run();