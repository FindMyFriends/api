<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Spot;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PutTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->connection, 'spot'))->try();
		(new Misc\SamplePostgresData($this->connection, 'demand_spot', ['spot_id' => $spot, 'demand_id' => $demand]))->try();
		$response = (new Endpoint\Spot\Put(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/spot/put.json')
				)
			),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $spot]);
		$demand = json_decode($response->body()->serialization(), true);
		Assert::null($demand);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test400OnBadInput(): void {
		Assert::exception(function () {
			(new Endpoint\Spot\Put(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				$this->connection,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property coordinates is required');
	}

	public function test404OnNotExisting(): void {
		Assert::exception(function () {
			(new Endpoint\Spot\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/spot/put.json')
					)
				),
				$this->connection,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Spot does not exist', HTTP_NOT_FOUND);
	}

	public function test403OnForeign(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'spot'))->try();
		Assert::exception(function () use ($seeker, $id) {
			(new Endpoint\Spot\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/spot/put.json')
					)
				),
				$this->connection,
				new Access\FakeSeeker((string) $seeker)
			))->response(['id' => $id]);
		}, \UnexpectedValueException::class, 'Spot does not belong to you.', HTTP_FORBIDDEN);
	}

	public function test400OnEmptyBody(): void {
		Assert::exception(function () {
			(new Endpoint\Spot\Put(
				new Application\FakeRequest(new Output\FakeFormat('{}')),
				$this->connection,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property coordinates is required');
	}
}

(new PutTest())->run();
