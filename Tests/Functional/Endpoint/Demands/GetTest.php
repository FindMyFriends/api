<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demands;

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
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker, 'general' => ['birth_year_range' => '(1996, 1999)']]))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker, 'general' => ['birth_year_range' => '(1996, 2000)']]))->try();
		(new Misc\SampleDemand($this->connection))->try();
		$response = (new Endpoint\Demands\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'demands', []),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		$demands = json_decode($response->body()->serialization());
		Assert::count(2, $demands);
		(new Misc\SchemaAssertion(
			$demands,
			new \SplFileInfo(__DIR__ . '/../../../../App/Endpoint/Demand/schema/get.json')
		))->assert();
	}

	public function testIncludedCountHeader(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->connection))->try();
		$headers = (new Endpoint\Demands\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'demands', []),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['page' => 1, 'per_page' => 10, 'sort' => ''])->headers();
		Assert::same(2, $headers['X-Total-Count']);
	}
}

(new GetTest())->run();
