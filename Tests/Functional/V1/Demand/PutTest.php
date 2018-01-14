<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Demand;

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
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$demand = json_decode(
			(new V1\Demand\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/put.json')
					)
				),
				new Uri\FakeUri('/', 'v1/demands/1', []),
				$this->database,
				new Access\FakeUser((string) $seeker)
			))->template(['id' => $id])->render(),
			true
		);
		Assert::null($demand);
		Assert::same(HTTP_NO_CONTENT, http_response_code());
	}

	public function test400OnBadInput() {
		['id' => $id] = (new Misc\SampleDemand($this->database))->try();
		$demand = json_decode(
			(new V1\Demand\Put(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new Uri\FakeUri('/', 'v1/demands/1', []),
				$this->database,
				new Access\FakeUser()
			))->template(['id' => $id])->render(),
			true
		);
		Assert::same(['message' => 'The property location is required'], $demand);
		Assert::same(HTTP_BAD_REQUEST, http_response_code());
	}

	public function test404OnNotExisting() {
		$demand = json_decode(
			(new V1\Demand\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/put.json')
					)
				),
				new Uri\FakeUri('/', 'v1/demands/1', []),
				$this->database,
				new Access\FakeUser()
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Demand does not exist'], $demand);
		Assert::same(HTTP_NOT_FOUND, http_response_code());
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database))->try();
		$demand = json_decode(
			(new V1\Demand\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/put.json')
					)
				),
				new Uri\FakeUri('/', 'v1/demands/1', []),
				$this->database,
				new Access\FakeUser((string) $seeker)
			))->template(['id' => $id])->render(),
			true
		);
		Assert::same(['message' => 'This is not your demand'], $demand);
		Assert::same(HTTP_FORBIDDEN, http_response_code());
	}
}

(new PutTest())->run();