<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demands;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->database))->try();
		$response = (new Endpoint\Demands\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'demands', []),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		$demands = json_decode($response->body()->serialization());
		Assert::count(2, $demands);
		(new Misc\SchemaAssertion(
			$demands,
			new \SplFileInfo(__DIR__ . '/../../../../App/Endpoint/Demand/schema/get.json')
		))->assert();
	}

	public function testIncludedCountHeader() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleDemand($this->database))->try();
		$headers = (new Endpoint\Demands\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'demands', []),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['page' => 1, 'per_page' => 10, 'sort' => ''])->headers();
		Assert::same(2, $headers['X-Total-Count']);
	}
}

(new GetTest())->run();
