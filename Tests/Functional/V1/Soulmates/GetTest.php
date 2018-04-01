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
		$demand1 = current((new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try());
		$demand2 = current((new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try());
		(new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand2]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand2]))->try();
		$soulmates = json_decode(
			(new V1\Soulmates\Get(
				$this->configuration['HASHIDS'],
				new Uri\FakeUri('/', 'v1/soulmates', []),
				$this->database,
				new Access\FakeUser($seeker),
				new Http\FakeRole(true),
				$this->elasticsearch
			))->template(['page' => 1, 'per_page' => 10])->render()
		);
		Assert::count(2, $soulmates);
	}

	public function testMatchingSchema() {
		$seeker = (string) current((new Misc\SamplePostgresData($this->database, 'seeker'))->try());
		$demand = current((new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try());
		(new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand]))->try();
		$soulmates = json_decode(
			(new V1\Soulmates\Get(
				$this->configuration['HASHIDS'],
				new Uri\FakeUri('/', 'v1/soulmates', []),
				$this->database,
				new Access\FakeUser($seeker),
				new Http\FakeRole(true),
				$this->elasticsearch
			))->template(['page' => 1, 'per_page' => 10])->render()
		);
		(new Misc\SchemaAssertion(
			$soulmates,
			(new \SplFileInfo(__DIR__ . '/../../../../App/V1/Soulmates/schema/get.json'))
		))->assert();
	}

	public function testUsingFilterByDemand() {
		$seeker = (string) current((new Misc\SamplePostgresData($this->database, 'seeker'))->try());
		$demand1 = current((new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try());
		$demand2 = current((new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try());
		(new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand2]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand2]))->try();
		$soulmates = json_decode(
			(new V1\Soulmates\Get(
				$this->configuration['HASHIDS'],
				new Uri\FakeUri('/', 'v1/soulmates', []),
				$this->database,
				new Access\FakeUser($seeker),
				new Http\FakeRole(true),
				$this->elasticsearch
			))->template(['page' => 1, 'per_page' => 10, 'demand_id' => $demand1])->render()
		);
		Assert::count(1, $soulmates);
	}

	public function testSuccessOnNoSoulmates() {
		$soulmates = json_decode(
			(new V1\Soulmates\Get(
				$this->configuration['HASHIDS'],
				new Uri\FakeUri('/', 'v1/soulmates', []),
				$this->database,
				new Access\FakeUser('1'),
				new Http\FakeRole(true),
				$this->elasticsearch
			))->template(['page' => 1, 'per_page' => 10])->render()
		);
		Assert::count(0, $soulmates);
	}
}

(new GetTest())->run();