<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Evolution\Spots;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class DeleteTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $change] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->connection, 'spot'))->try();
		(new Misc\SamplePostgresData($this->connection, 'evolution_spot', ['evolution_id' => $change, 'spot_id' => $spot]))->try();
		$response = (new Endpoint\Evolution\Spots\Delete(
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $spot]);
		Assert::same('', $response->body()->serialization());
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test403ForNotOwned(): void {
		Assert::exception(function () {
			(new Endpoint\Evolution\Spots\Delete(
				$this->connection,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Spot does not belong to you.', HTTP_FORBIDDEN);
	}
}

(new DeleteTest())->run();
