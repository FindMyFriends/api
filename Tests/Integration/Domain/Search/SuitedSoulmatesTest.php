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
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general' => ['gender' => 'man']]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general' => ['gender' => 'man']]]);
		$id = (new Storage\NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		(new Search\SuitedSoulmates($id, $seeker, $this->elasticsearch, $this->database))->seek($id);
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
				'index' => 'relationships',
				'type' => 'evolutions',
				'body' => ['id' => 2, 'general' => ['gender' => 'man'], 'seeker_id' => 1],
			]
		);
		$id = (new Storage\NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		(new Search\SuitedSoulmates($id, $seeker, $this->elasticsearch, $this->database))->seek($id);
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
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general' => ['gender' => 'man']]]);
		$id = (new Storage\NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		$soulmates = new Search\SuitedSoulmates($id, $seeker, $this->elasticsearch, $this->database);
		$soulmates->seek();
		$soulmates->seek();
		(new Storage\NativeQuery(
			$this->database,
			"INSERT INTO soulmate_requests (demand_id, status)
			SELECT id, 'pending' FROM demands"
		))->execute();
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
		['id' => $seekerId] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seekerId]))->try();
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO soulmate_requests (demand_id, status) VALUES
			(?, ?), (?, ?), (?, ?)',
			[
				(new Storage\NativeQuery(
					$this->database,
					'INSERT INTO soulmates (demand_id, evolution_id, score)
					VALUES (?, ?, 20)
					RETURNING demand_id',
					[$demand, (new Misc\SampleEvolution($this->database))->try()['id']]
				))->field(),
				'pending',
				(new Storage\NativeQuery(
					$this->database,
					'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES
					(?, ?, 30)
					RETURNING demand_id',
					[$demand, (new Misc\SampleEvolution($this->database))->try()['id']]
				))->field(),
				'pending',
				(new Storage\NativeQuery(
					$this->database,
					'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES
					(?, ?, 5)
					RETURNING demand_id',
					[(new Misc\SampleDemand($this->database))->try()['id'], (new Misc\SampleEvolution($this->database))->try()['id']]
				))->field(),
				'pending',
			]
		))->execute();
		$soulmates = new Search\SuitedSoulmates(
			$demand,
			new Access\FakeUser((string) $seekerId),
			$this->elasticsearch,
			$this->database
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
		Assert::same(1, $soulmate['evolution_id']);
		Assert::same(1, $soulmate['demand_id']);
		Assert::same($seekerId, $soulmate['seeker_id']);
		$matches->next();
		Assert::null($matches->current());
	}
}

(new SuitedSoulmatesTest())->run();