<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Evolution;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class Put extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		$evolution = json_decode(
			(new V1\Evolution\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/put.json')
					)
				),
				new Uri\FakeUri('/', 'v1/evolutions', []),
				$this->database,
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::null($evolution);
		Assert::same(204, http_response_code());
	}

	public function test400OnBadInput() {
		(new Misc\SampleEvolution($this->database))->try();
		$evolution = json_decode(
			(new V1\Evolution\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						'{"name":"bar"}'
					)
				),
				new Uri\FakeUri('/', 'v1/evolutions/1', []),
				$this->database,
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'The property general is required'], $evolution);
		Assert::same(400, http_response_code());
	}

	public function test404OnNotExisting() {
		$evolution = json_decode(
			(new V1\Evolution\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/put.json')
					)
				),
				new Uri\FakeUri('/', 'v1/evolutions/1', []),
				$this->database,
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Evolution change 1 does not exist'], $evolution);
		Assert::same(404, http_response_code());
	}
}

(new Put())->run();