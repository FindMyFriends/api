<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class GetTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		(new Misc\SampleEvolution($this->connection))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker, 'general' => ['birth_year' => 1996]]))->try();
		$response = (new Endpoint\Evolution\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'evolutions/1', []),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$evolution = json_decode($response->body()->serialization());
		Assert::same((new Hashids())->encode($id), $evolution->id);
		(new Misc\SchemaAssertion(
			$evolution,
			new \SplFileInfo(__DIR__ . '/../../../../App/Endpoint/Evolution/schema/get.json')
		))->assert();
	}

	public function test403ForNotOwned(): void {
		Assert::exception(function () {
			(new Endpoint\Evolution\Get(
				new Hashids(),
				new Uri\FakeUri('/', 'evolutions/1', []),
				$this->connection,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Evolution change does not belong to you.', HTTP_FORBIDDEN);
	}
}

(new GetTest())->run();
