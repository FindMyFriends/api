<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Access;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class SuitedSoulmatesTest extends Tester\TestCase {
	use TestCase\Search;

	public function testPersistingMatches() {
		(new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => 2]))->try();
		$seeker = new Access\FakeUser('1');
		(new Domain\IndividualDemands(
			$seeker,
			$this->database
		))->ask(json_decode(file_get_contents(__DIR__ . '/samples/demand.json'), true));
		$evolution = function(): void {
			(new Evolution\IndividualChain(
				new Access\FakeUser('2'),
				$this->database
			))->extend(json_decode(file_get_contents(__DIR__ . '/samples/evolution.json'), true));
		};
		$evolution();
		$evolution();
		static $params = [
			'refresh' => true,
			'index' => 'soulmates',
			'type' => 'evolutions',
		];
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general' => ['gender' => 'man']]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general' => ['gender' => 'man']]]);
		$id = (new Storage\NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		(new Search\SuitedSoulmates($seeker, $this->elasticsearch, $this->database))->find($id);
		Assert::same(
			[
				['demand_id' => $id, 'evolution_id' => 2, 'version' => 1],
				['demand_id' => $id, 'evolution_id' => 3, 'version' => 1],
			],
			(new Storage\NativeQuery(
				$this->database,
				'SELECT demand_id, evolution_id, version FROM soulmates ORDER BY evolution_id'
			))->rows()
		);
	}

	public function testIgnoringOwnEvolutions() {
		(new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => 1]))->try();
		$seeker = new Access\FakeUser('1');
		(new Domain\IndividualDemands(
			$seeker,
			$this->database
		))->ask(json_decode(file_get_contents(__DIR__ . '/samples/demand.json'), true));
		(new Evolution\IndividualChain(
			new Access\FakeUser('1'),
			$this->database
		))->extend(json_decode(file_get_contents(__DIR__ . '/samples/evolution.json'), true));
		$this->elasticsearch->index(
			[
				'refresh' => true,
				'index' => 'soulmates',
				'type' => 'evolutions',
				'body' => ['id' => 2, 'general' => ['gender' => 'man'], 'seeker_id' => 1],
			]
		);
		$id = (new Storage\NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		(new Search\SuitedSoulmates($seeker, $this->elasticsearch, $this->database))->find($id);
		Assert::same([], (new Storage\NativeQuery($this->database, 'SELECT * FROM soulmates'))->rows());
	}

	public function testMultiMatchCausingIncrementingVersion() {
		(new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => 2]))->try();
		$seeker = new Access\FakeUser('1');
		(new Domain\IndividualDemands(
			$seeker,
			$this->database
		))->ask(json_decode(file_get_contents(__DIR__ . '/samples/demand.json'), true));
		(new Evolution\IndividualChain(
			new Access\FakeUser('2'),
			$this->database
		))->extend(json_decode(file_get_contents(__DIR__ . '/samples/evolution.json'), true));
		static $params = [
			'refresh' => true,
			'index' => 'soulmates',
			'type' => 'evolutions',
		];
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general' => ['gender' => 'man']]]);
		$id = (new Storage\NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		$soulmates = new Search\SuitedSoulmates($seeker, $this->elasticsearch, $this->database);
		$soulmates->find($id);
		$soulmates->find($id);
		Assert::same(
			[['demand_id' => $id, 'evolution_id' => 2, 'version' => 2]],
			(new Storage\NativeQuery(
				$this->database,
				'SELECT demand_id, evolution_id, version FROM soulmates'
			))->rows()
		);
		Assert::same(1, $soulmates->count(new Dataset\EmptySelection()));
	}

	public function testFromParticularDemand() {
		$seekerId = (new Misc\SamplePostgresData($this->database, 'seeker'))->try()['id'];
		$demand = (new Misc\SampleDemand($this->database, ['seeker_id' => $seekerId]))->try()['id'];
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES (?, ?, 20)',
			[$demand, (new Misc\SampleEvolution($this->database))->try()['id']]
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES (?, ?, 30)',
			[$demand, (new Misc\SampleEvolution($this->database))->try()['id']]
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES (?, ?, 5)',
			[(new Misc\SampleDemand($this->database))->try()['id'], (new Misc\SampleEvolution($this->database))->try()['id']]
		))->execute();
		$soulmates = new Search\SuitedSoulmates(
			new Access\FakeUser(strval($seekerId)),
			$this->elasticsearch,
			$this->database
		);
		$selection = new Dataset\FakeSelection(['filter' => ['demand_id' => $demand]]);
		$matches = $soulmates->matches($selection);
		Assert::same(2, $soulmates->count($selection));
		$soulmate = $matches->current();
		Assert::equal(
			['id' => 2, 'new' => true, 'demand_id' => 1, 'position' => 1, 'seeker_id' => $seekerId, 'evolution_id' => 2],
			json_decode($soulmate->print(new Output\Json())->serialization(), true)
		);
		$matches->next();
		$soulmate = $matches->current();
		Assert::equal(
			['id' => 1, 'new' => true, 'demand_id' => 1, 'position' => 2, 'seeker_id' => $seekerId, 'evolution_id' => 1],
			json_decode($soulmate->print(new Output\Json())->serialization(), true)
		);
		$matches->next();
		Assert::null($matches->current());
	}

	public function testAddingToSearchLog() {
		(new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => 2]))->try();
		$seeker = new Access\FakeUser('1');
		(new Domain\IndividualDemands(
			$seeker,
			$this->database
		))->ask(json_decode(file_get_contents(__DIR__ . '/samples/demand.json'), true));
		(new Evolution\IndividualChain(
			new Access\FakeUser('2'),
			$this->database
		))->extend(json_decode(file_get_contents(__DIR__ . '/samples/evolution.json'), true));
		static $params = [
			'refresh' => true,
			'index' => 'soulmates',
			'type' => 'evolutions',
		];
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general' => ['gender' => 'man']]]);
		$id = (new Storage\NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		(new Search\SuitedSoulmates($seeker, $this->elasticsearch, $this->database))->find($id);
		Assert::same(
			[['demand_id' => $id]],
			(new Storage\NativeQuery($this->database, 'SELECT demand_id FROM soulmate_searches'))->rows()
		);
	}
}

(new SuitedSoulmatesTest())->run();