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
use Klapuch\Output;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class SuitedSoulmatesTest extends TestCase\Runtime {
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
		(new Search\SuitedSoulmates($id, $this->elasticsearch, $this->connection))->seek();
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
		(new Search\SuitedSoulmates($id, $this->elasticsearch, $this->connection))->seek();
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
		$soulmates = new Search\SuitedSoulmates($id, $this->elasticsearch, $this->connection);
		$soulmates->seek();
		$soulmates->seek();
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

	public function testFromParticularDemand(): void {
		['id' => $seekerId] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seekerId]))->try();
		['id' => $otherDemand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seekerId]))->try();
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO soulmates (demand_id, evolution_id, score, is_exposed)
			 VALUES (?, ?, 20, FALSE)',
			[$demand, (new Misc\SampleEvolution($this->connection))->try()['id']]
		))->execute();
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO soulmates (demand_id, evolution_id, score, is_exposed) VALUES
			(?, ?, 30, TRUE)',
			[$demand, (new Misc\SampleEvolution($this->connection))->try()['id']]
		))->execute();
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO soulmates (demand_id, evolution_id, score, is_exposed) VALUES
			(?, ?, 5, FALSE)
			RETURNING demand_id',
			[$otherDemand, (new Misc\SampleEvolution($this->connection))->try()['id']]
		))->execute();
		$selfId = (new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO soulmate_requests (demand_id, status) VALUES
			(?, ?)
			RETURNING id',
			[$demand, 'pending']
		))->field();
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO soulmate_requests (demand_id, status, self_id) VALUES
			(?, ?, ?)',
			[$demand, 'processing', $selfId]
		))->field();
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO soulmate_requests (demand_id, status) VALUES
			(?, ?)',
			[$otherDemand, 'pending']
		))->field();
		$soulmates = new Search\SuitedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->connection
		);
		$matches = $soulmates->matches(new Dataset\EmptySelection());
		Assert::same(2, $soulmates->count(new Dataset\EmptySelection()));
		$current = $matches->current();
		$soulmate = json_decode($current->print(new Output\Json())->serialization(), true);
		Assert::same(2, $soulmate['evolution_id']);
		Assert::same(1, $soulmate['demand_id']);
		Assert::same($seekerId, $soulmate['seeker_id']);
		$matches->next();
		$current = $matches->current();
		$soulmate = json_decode($current->print(new Output\Json())->serialization(), true);
		Assert::same(null, $soulmate['evolution_id']);
		Assert::same(1, $soulmate['demand_id']);
		Assert::same($seekerId, $soulmate['seeker_id']);
		$matches->next();
		Assert::null($matches->current());
	}
}

(new SuitedSoulmatesTest())->run();
