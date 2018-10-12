<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Seeker;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class GetTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponseWithPrivateParts(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Misc\SampleEvolution(
			$this->connection,
			[
				'seeker_id' => $id,
				'general' => [
					'firstname' => 'Dominik',
					'lastname' => 'Klapuch',
					'birth_year' => 1996,
					'ethnic_group_id' => 1,
					'sex' => 'man',
				],
			]
		))->try();
		(new Misc\SamplePostgresData(
			$this->connection,
			'seeker_contact',
			['seeker_id' => $id, 'facebook' => 'test_fb', 'instagram' => 'test_ig', 'phone_number' => null]
		))->try();
		['id' => $met] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $id]))->try();
		['id' => $evolution] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $met]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand, 'evolution_id' => $evolution, 'is_exposed' => true]))->try();
		$payload = json_decode(
			(new Endpoint\Seeker\Get(
				$this->connection,
				new Access\FakeSeeker((string) $id)
			))->response(['id' => $met])->body()->serialization()
		);
		(new Misc\SchemaAssertion($payload, new \SplFileInfo(Endpoint\Seeker\Get::SCHEMA)))->assert();
		Assert::false(isset($payload->email));
	}

	public function testAllInfoForMyself(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Misc\SampleEvolution(
			$this->connection,
			[
				'seeker_id' => $id,
				'general' => [
					'firstname' => 'Dominik',
					'lastname' => 'Klapuch',
					'birth_year' => 1996,
					'ethnic_group_id' => 1,
					'sex' => 'man',
				],
			]
		))->try();
		(new Misc\SamplePostgresData(
			$this->connection,
			'seeker_contact',
			['seeker_id' => $id, 'facebook' => 'test_fb', 'instagram' => 'test_ig', 'phone_number' => null]
		))->try();
		$payload = json_decode(
			(new Endpoint\Seeker\Get(
				$this->connection,
				new Access\FakeSeeker((string) $id)
			))->response(['id' => $id])->body()->serialization()
		);
		(new Misc\SchemaAssertion($payload, new \SplFileInfo(Endpoint\Seeker\Get::SCHEMA)))->assert();
		Assert::true(isset($payload->email));
	}

	public function test404ForUnknown(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@bar.cz']))->try();
		Assert::exception(function () use ($id): void {
			(new Endpoint\Seeker\Get(
				$this->connection,
				new Access\FakeSeeker((string) $id)
			))->response(['id' => 666])->body()->serialization();
		}, \UnexpectedValueException::class, 'Seeker 666 is unknown', HTTP_NOT_FOUND);
	}
}

(new GetTest())->run();
