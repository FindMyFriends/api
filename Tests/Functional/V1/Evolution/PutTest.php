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
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PutTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$evolution = json_decode(
			(new V1\Evolution\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/put.json')
					)
				),
				new Uri\FakeUri('/', 'v1/evolutions/1', []),
				$this->database,
				new Access\FakeUser((string) $seeker),
				$this->redis
			))->template(['id' => $id])->render(),
			true
		);
		Assert::null($evolution);
		Assert::same(HTTP_NO_CONTENT, http_response_code());
	}

	public function test400OnBadInput() {
		(new Misc\SampleEvolution($this->database))->try();
		$evolution = json_decode(
			(new V1\Evolution\Put(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new Uri\FakeUri('/', 'v1/evolutions/1', []),
				$this->database,
				new Access\FakeUser(),
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'The property general is required'], $evolution);
		Assert::same(HTTP_BAD_REQUEST, http_response_code());
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
				new Access\FakeUser(),
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Evolution change 1 does not exist'], $evolution);
		Assert::same(HTTP_NOT_FOUND, http_response_code());
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database))->try();
		$evolution = json_decode(
			(new V1\Evolution\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/put.json')
					)
				),
				new Uri\FakeUri('/', 'v1/evolutions/1', []),
				$this->database,
				new Access\FakeUser((string) $seeker),
				$this->redis
			))->template(['id' => $id])->render(),
			true
		);
		Assert::same(['message' => sprintf('%d is not your evolution change', $id)], $evolution);
		Assert::same(HTTP_FORBIDDEN, http_response_code());
	}
}

(new PutTest())->run();