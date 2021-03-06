<?php
declare(strict_types = 1);

namespace FindMyFriends\Elastic;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class DemandedSoulmatesTest extends TestCase\Runtime {
	use TestCase\Search;

	public function testEliminatingSex(): void {
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		['id' => $demand] = (new Misc\SamplePostgresData($this->connection, 'nullable_demand', ['seeker_id' => $seeker, 'general' => ['sex' => 'woman']]))->try();
		$this->elasticsearch->index($params + ['body' => ['id' => 1, 'general.sex' => 'random']]);
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general.sex' => 'man']]);
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general.sex' => 'woman']]);
		(new Search\DemandedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->connection
		))->matches(new Dataset\EmptySelection());
		Assert::same(
			[['evolution_id' => 3]],
			(new Storage\NativeQuery($this->connection, 'SELECT evolution_id FROM soulmates'))->rows()
		);
	}

	public function testMatchingHairColorsWithSimilar(): void {
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		['id' => $demand] = (new Misc\SamplePostgresData($this->connection, 'nullable_demand', ['seeker_id' => $seeker, 'general' => ['sex' => 'woman'], 'hair' => ['color_id' => 8]]))->try();
		$this->elasticsearch->index($params + ['body' => ['id' => 1, 'general.sex' => 'woman', 'hair.color_id' => 8]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general.sex' => 'woman', 'hair.color_id' => 12]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general.sex' => 'woman', 'hair.color_id' => 2]]);
		(new Search\DemandedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->connection
		))->matches(new Dataset\EmptySelection());
		$soulmates = (new Storage\NativeQuery($this->connection, 'SELECT * FROM soulmates ORDER BY evolution_id'))->rows();
		Assert::count(3, $soulmates);
		Assert::true($soulmates[0]['score'] > $soulmates[1]['score']);
		Assert::true($soulmates[1]['score'] > $soulmates[2]['score']);
	}

	public function testBoostingLastnameOverFirstname(): void {
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		['id' => $demand] = (new Misc\SamplePostgresData(
			$this->connection,
			'nullable_demand',
			['seeker_id' => $seeker, 'general' => ['sex' => 'man', 'firstname' => 'Dominik', 'lastname' => 'Klapuch']]
		))->try();
		$this->elasticsearch->index($params + ['body' => ['id' => 1, 'general.sex' => 'man', 'general.lastname' => 'Klapuch']]);
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general.sex' => 'man', 'general.firstname' => 'Dominik']]);
		(new Search\DemandedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->connection
		))->matches(new Dataset\EmptySelection());
		$soulmates = (new Storage\NativeQuery($this->connection, 'SELECT * FROM soulmates ORDER BY evolution_id'))->rows();
		Assert::count(2, $soulmates);
		Assert::true($soulmates[0]['score'] > $soulmates[1]['score']);
	}

	public function testNotMatchingColorButMatchDifference(): void {
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SampleEvolution($this->connection))->try();
		['id' => $demand] = (new Misc\SamplePostgresData(
			$this->connection,
			'nullable_demand',
			['seeker_id' => $seeker, 'general' => ['sex' => 'man'], 'left_eye' => ['color_id' => 8], 'right_eye' => ['color_id' => 10]]
		))->try();
		$this->elasticsearch->index($params + ['body' => ['id' => 1, 'general.sex' => 'man', 'right_eye.color_id' => 10, 'left_eye.color_id' => 8]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general.sex' => 'man', 'right_eye.color_id' => 3, 'left_eye.color_id' => 4]]); // diff
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general.sex' => 'man', 'right_eye.color_id' => 5, 'left_eye.color_id' => 5]]);
		(new Search\DemandedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->connection
		))->matches(new Dataset\EmptySelection());
		$soulmates = (new Storage\NativeQuery($this->connection, 'SELECT * FROM soulmates ORDER BY evolution_id'))->rows();
		Assert::count(2, $soulmates);
		Assert::true($soulmates[0]['score'] > $soulmates[1]['score']);
	}
}

(new DemandedSoulmatesTest())->run();
