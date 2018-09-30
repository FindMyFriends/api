<?php
declare(strict_types = 1);

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

/**
 * @testCase
 */
final class PatchTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Demand\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/patch.json')
				)
			),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test400OnBadInput() {
		Assert::exception(function () {
			(new Endpoint\Demand\Patch(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				$this->connection,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property note is required');
	}

	public function test404OnNotExisting() {
		Assert::exception(function () {
			(new Endpoint\Demand\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/patch.json')
					)
				),
				$this->connection,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Demand does not exist', HTTP_NOT_FOUND);
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleDemand($this->connection))->try();
		Assert::exception(function () use ($seeker, $id) {
			(new Endpoint\Demand\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/patch.json')
					)
				),
				$this->connection,
				new Access\FakeSeeker((string) $seeker)
			))->response(['id' => $id]);
		}, \UnexpectedValueException::class, 'This is not your demand', HTTP_FORBIDDEN);
	}

	public function test400OnEmptyBody() {
		Assert::exception(function () {
			(new Endpoint\Demand\Patch(
				new Application\FakeRequest(new Output\FakeFormat('{}')),
				$this->connection,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property note is required');
	}
}

(new PatchTest())->run();
