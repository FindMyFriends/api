<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class DemandedSoulmatesTest extends TestCase\Runtime {
	use TestCase\Search;

	public function testPersistingMatches(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection, ['seeker_id' => 2]))->try();
		(new Interaction\IndividualDemands(
			new Access\FakeSeeker('1'),
			$this->connection
		))->ask(json_decode(file_get_contents(__DIR__ . '/samples/demand.json'), true));
		$evolution = function(): void {
			(new Evolution\IndividualChain(
				new Access\FakeSeeker('2'),
				$this->connection
			))->extend(json_decode(file_get_contents(__DIR__ . '/samples/evolution.json'), true));
		};
		$evolution();
		$evolution();
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general' => ['sex' => 'man']]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general' => ['sex' => 'man']]]);
		$id = (new Storage\NativeQuery($this->connection, 'SELECT id FROM demands'))->field();
		(new Search\DemandedSoulmates($id, $this->elasticsearch, $this->connection))->matches(new Dataset\EmptySelection());
		Assert::same(
			[
				['demand_id' => $id, 'evolution_id' => 2, 'version' => 1],
				['demand_id' => $id, 'evolution_id' => 3, 'version' => 1],
			],
			(new Storage\NativeQuery(
				$this->connection,
				'SELECT demand_id, evolution_id, version FROM soulmates ORDER BY evolution_id'
			))->rows()
		);
	}

	public function testIgnoringOwnEvolutions(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection, ['seeker_id' => 1]))->try();
		(new Interaction\IndividualDemands(
			new Access\FakeSeeker('1'),
			$this->connection
		))->ask(json_decode(file_get_contents(__DIR__ . '/samples/demand.json'), true));
		(new Evolution\IndividualChain(
			new Access\FakeSeeker('1'),
			$this->connection
		))->extend(json_decode(file_get_contents(__DIR__ . '/samples/evolution.json'), true));
		$this->elasticsearch->index(
			[
				'refresh' => true,
				'index' => 'relationships',
				'type' => 'evolutions',
				'body' => ['id' => 2, 'general' => ['sex' => 'man'], 'seeker_id' => 1],
			]
		);
		$id = (new Storage\NativeQuery($this->connection, 'SELECT id FROM demands'))->field();
		(new Search\DemandedSoulmates($id, $this->elasticsearch, $this->connection))->matches(new Dataset\EmptySelection());
		Assert::same([], (new Storage\NativeQuery($this->connection, 'SELECT * FROM soulmates'))->rows());
	}

	public function testMultiMatchCausingIncrementingVersion(): void {
		(new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection, ['seeker_id' => 2]))->try();
		(new Interaction\IndividualDemands(
			new Access\FakeSeeker('1'),
			$this->connection
		))->ask(json_decode(file_get_contents(__DIR__ . '/samples/demand.json'), true));
		(new Evolution\IndividualChain(
			new Access\FakeSeeker('2'),
			$this->connection
		))->extend(json_decode(file_get_contents(__DIR__ . '/samples/evolution.json'), true));
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general' => ['sex' => 'man']]]);
		$id = (new Storage\NativeQuery($this->connection, 'SELECT id FROM demands'))->field();
		$soulmates = new Search\DemandedSoulmates($id, $this->elasticsearch, $this->connection);
		$soulmates->matches(new Dataset\EmptySelection());
		$soulmates->matches(new Dataset\EmptySelection());
		(new Storage\NativeQuery(
			$this->connection,
			"INSERT INTO soulmate_requests (demand_id, status)
			SELECT id, 'pending' FROM demands"
		))->execute();
		Assert::same(
			[['demand_id' => $id, 'evolution_id' => 2, 'version' => 2]],
			(new Storage\NativeQuery(
				$this->connection,
				'SELECT demand_id, evolution_id, version FROM soulmates'
			))->rows()
		);
		Assert::same(1, $soulmates->count(new Dataset\EmptySelection()));
	}
}

(new DemandedSoulmatesTest())->run();
