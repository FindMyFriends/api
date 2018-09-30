<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Demand\Soulmates;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

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
		$response = (new Endpoint\Demand\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'soulmates', []),
			$this->connection,
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => $demand1, 'sort' => '']);
		Assert::count(1, json_decode($response->body()->serialization()));
		(new Misc\SchemaAssertion(
			json_decode($response->body()->serialization()),
			(new \SplFileInfo(Endpoint\Demand\Soulmates\Get::SCHEMA))
		))->assert();
	}

	public function testSuccessOnNoSoulmates(): void {
		$response = (new Endpoint\Demand\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'soulmates', []),
			$this->connection,
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => 1, 'sort' => '']);
		Assert::count(0, json_decode($response->body()->serialization()));
	}

	public function testIncludedCountHeader(): void {
		$headers = (new Endpoint\Demand\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'soulmates', []),
			$this->connection,
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => 1, 'sort' => ''])->headers();
		Assert::same(0, $headers['X-Total-Count']);
	}
}

(new GetTest())->run();
