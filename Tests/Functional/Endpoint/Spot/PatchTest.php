<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Spot;

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
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->database, 'spot'))->try();
		(new Misc\SamplePostgresData($this->database, 'demand_spot', ['spot_id' => $spot, 'demand_id' => $demand]))->try();
		$response = (new Endpoint\Spot\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/spot/patch.json')
				)
			),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $spot]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test400OnBadInput() {
		Assert::exception(function () {
			(new Endpoint\Spot\Patch(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				$this->database,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property coordinates is required');
	}

	public function test404OnNotExisting() {
		Assert::exception(function () {
			(new Endpoint\Spot\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/spot/patch.json')
					)
				),
				$this->database,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Spot does not exist', HTTP_NOT_FOUND);
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'spot'))->try();
		Assert::exception(function () use ($seeker, $id) {
			(new Endpoint\Spot\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/spot/patch.json')
					)
				),
				$this->database,
				new Access\FakeSeeker((string) $seeker)
			))->response(['id' => $id]);
		}, \UnexpectedValueException::class, 'Spot does not belong to you.', HTTP_FORBIDDEN);
	}

	public function test400OnEmptyBody() {
		Assert::exception(function () {
			(new Endpoint\Spot\Patch(
				new Application\FakeRequest(new Output\FakeFormat('{}')),
				$this->database,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property coordinates is required');
	}
}

(new PatchTest())->run();
