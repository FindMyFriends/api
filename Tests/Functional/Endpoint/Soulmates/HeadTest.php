<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Soulmates;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class HeadTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testEmptyResponse(): void {
		$seeker = (string) current((new Misc\SamplePostgresData($this->connection, 'seeker'))->try());
		['id' => $demand1] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate_request', ['demand_id' => $demand1]))->try();
		$response = (new Endpoint\Soulmates\Head(
			new Uri\FakeUri('/', 'soulmates', []),
			$this->connection,
			new Access\FakeSeeker($seeker)
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => (string) $demand1]);
		Assert::null(json_decode($response->body()->serialization()));
	}

	public function testNeededHeaders(): void {
		$seeker = (string) current((new Misc\SamplePostgresData($this->connection, 'seeker'))->try());
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		$headers = (new Endpoint\Soulmates\Head(
			new Uri\FakeUri('/', 'soulmates', []),
			$this->connection,
			new Access\FakeSeeker($seeker)
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => (string) $demand])->headers();
		Assert::count(3, $headers);
		Assert::same(0, $headers['X-Total-Count']);
		Assert::same('text/plain', $headers['Content-Type']);
		Assert::true(isset($headers['Link']));
	}
}

(new HeadTest())->run();
