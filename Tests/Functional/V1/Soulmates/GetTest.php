<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Functional\V1\Soulmates;

use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		$seeker = (string) current((new Misc\SamplePostgresData($this->database, 'seeker'))->try());
		['id' => $demand1] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $demand2] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand2]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand2]))->try();
		$response = (new V1\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'v1/soulmates', []),
			$this->database,
			new Access\FakeUser($seeker),
			new Http\FakeRole(true),
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => $demand1, 'sort' => '']);
		Assert::count(1, json_decode($response->body()->serialization()));
		(new Misc\SchemaAssertion(
			json_decode($response->body()->serialization()),
			(new \SplFileInfo(__DIR__ . '/../../../../App/V1/Soulmates/schema/get.json'))
		))->assert();
	}

	public function testSuccessOnNoSoulmates() {
		$response = (new V1\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'v1/soulmates', []),
			$this->database,
			new Access\FakeUser('1'),
			new Http\FakeRole(true),
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => 1, 'sort' => '']);
		Assert::count(0, json_decode($response->body()->serialization()));
	}

	public function testIncludedCountHeader() {
		$headers = (new V1\Soulmates\Get(
			$this->configuration['HASHIDS'],
			new Uri\FakeUri('/', 'v1/soulmates', []),
			$this->database,
			new Access\FakeUser('1'),
			new Http\FakeRole(true),
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => 1, 'sort' => ''])->headers();
		Assert::same(0, $headers['X-Total-Count']);
	}
}

(new GetTest())->run();
