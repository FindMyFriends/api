<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Demand;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PatchTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Demand\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/patch.json')
				)
			),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test400OnBadInput() {
		$response = (new Endpoint\Demand\Patch(
			new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
			$this->database,
			new Access\FakeSeeker()
		))->response(['id' => 1]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'The property name is not defined and the definition does not allow additional properties'], $demand);
		Assert::same(HTTP_BAD_REQUEST, $response->status());
	}

	public function test404OnNotExisting() {
		$response = (new Endpoint\Demand\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/patch.json')
				)
			),
			$this->database,
			new Access\FakeSeeker()
		))->response(['id' => 1]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Demand does not exist'], $demand);
		Assert::same(HTTP_NOT_FOUND, $response->status());
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database))->try();
		$response = (new Endpoint\Demand\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/patch.json')
				)
			),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'This is not your demand'], $demand);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}
}

(new PatchTest())->run();
