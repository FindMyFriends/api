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
final class GetTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		$seeker = (string) current((new Misc\SamplePostgresData($this->connection, 'seeker'))->try());
		['id' => $demand1] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $demand2] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand2]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate_request', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate_request', ['demand_id' => $demand2]))->try();
		$response = (new Endpoint\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'soulmates', []),
			$this->connection,
			new Access\FakeSeeker($seeker)
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		Assert::count(2, json_decode($response->body()->serialization()));
		(new Misc\SchemaAssertion(
			json_decode($response->body()->serialization()),
			(new \SplFileInfo(Endpoint\Soulmates\Get::SCHEMA))
		))->assert();
	}

	public function testSuccessOnNoSoulmates(): void {
		$response = (new Endpoint\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'soulmates', []),
			$this->connection,
			new Access\FakeSeeker('1')
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		Assert::count(0, json_decode($response->body()->serialization()));
	}

	public function testIncludedCountHeader(): void {
		$headers = (new Endpoint\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'soulmates', []),
			$this->connection,
			new Access\FakeSeeker('1')
		))->response(['page' => 1, 'per_page' => 10, 'sort' => ''])->headers();
		Assert::same(0, $headers['X-Total-Count']);
	}
}

(new GetTest())->run();
