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
		$response = (new V1\Demand\Put(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/put.json')
				)
			),
			new Uri\FakeUri('/', 'v1/demands/1', []),
			$this->database,
			new Access\FakeUser((string) $seeker)
		))->response(['id' => $id]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test400OnBadInput() {
		['id' => $id] = (new Misc\SampleDemand($this->database))->try();
		$response = (new V1\Demand\Put(
			new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
			new Uri\FakeUri('/', 'v1/demands/1', []),
			$this->database,
			new Access\FakeUser()
		))->response(['id' => $id]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'The property location is required'], $demand);
		Assert::same(HTTP_BAD_REQUEST, $response->status());
	}

	public function test404OnNotExisting() {
		$response = (new V1\Demand\Put(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/put.json')
				)
			),
			new Uri\FakeUri('/', 'v1/demands/1', []),
			$this->database,
			new Access\FakeUser()
		))->response(['id' => 1]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Demand does not exist'], $demand);
		Assert::same(HTTP_NOT_FOUND, $response->status());
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database))->try();
		$response = (new V1\Demand\Put(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/put.json')
				)
			),
			new Uri\FakeUri('/', 'v1/demands/1', []),
			$this->database,
			new Access\FakeUser((string) $seeker)
		))->response(['id' => $id]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'This is not your demand'], $demand);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}
}

(new PutTest())->run();