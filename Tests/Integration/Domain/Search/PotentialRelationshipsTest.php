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
use FindMyFriends\Misc\SampleEvolution;
use FindMyFriends\Misc\SamplePostgresData;
use FindMyFriends\TestCase;
use Klapuch\Access;
use Klapuch\Storage\NativeQuery;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PotentialRelationshipsTest extends Tester\TestCase {
	use TestCase\Search;

	public function testPersistingMatches() {
		(new SamplePostgresData($this->database, 'seeker'))->try();
		(new SamplePostgresData($this->database, 'seeker'))->try();
		(new SampleEvolution($this->database, ['seeker_id' => 2]))->try();
		(new Domain\IndividualDemands(
			new Access\FakeUser('1'),
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
		$id = (new NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		(new Search\PotentialRelationships($id, $this->elasticsearch, $this->database))->find();
		Assert::same(
			[
				['demand_id' => $id, 'evolution_id' => 2, 'version' => 1],
				['demand_id' => $id, 'evolution_id' => 3, 'version' => 1],
			],
			(new NativeQuery(
				$this->database,
				'SELECT demand_id, evolution_id, version FROM relationships ORDER BY evolution_id'
			))->rows()
		);
	}

	public function testIgnoringOwnEvolutions() {
		(new SamplePostgresData($this->database, 'seeker'))->try();
		(new SampleEvolution($this->database, ['seeker_id' => 1]))->try();
		(new Domain\IndividualDemands(
			new Access\FakeUser('1'),
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
		$id = (new NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		(new Search\PotentialRelationships($id, $this->elasticsearch, $this->database))->find();
		Assert::same([], (new NativeQuery($this->database, 'SELECT * FROM relationships'))->rows());
	}

	public function testMultiMatchCausingIncrementingVersion() {
		(new SamplePostgresData($this->database, 'seeker'))->try();
		(new SamplePostgresData($this->database, 'seeker'))->try();
		(new SampleEvolution($this->database, ['seeker_id' => 2]))->try();
		(new Domain\IndividualDemands(
			new Access\FakeUser('1'),
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
		$id = (new NativeQuery($this->database, 'SELECT id FROM demands'))->field();
		(new Search\PotentialRelationships($id, $this->elasticsearch, $this->database))->find();
		(new Search\PotentialRelationships($id, $this->elasticsearch, $this->database))->find();
		Assert::same(
			[['demand_id' => $id, 'evolution_id' => 2, 'version' => 2]],
			(new NativeQuery(
				$this->database,
				'SELECT demand_id, evolution_id, version FROM relationships'
			))->rows()
		);
	}
}

(new PotentialRelationshipsTest())->run();