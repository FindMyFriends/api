<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Elastic;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class SuitedSoulmatesTest extends Tester\TestCase {
	use TestCase\Search;

	public function testEliminatingSex() {
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $demand] = (new Misc\SamplePostgresData($this->database, 'nullable_demand', ['seeker_id' => $seeker, 'general' => ['sex' => 'woman']]))->try();
		$this->elasticsearch->index($params + ['body' => ['id' => 1, 'general.sex' => 'random']]);
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general.sex' => 'man']]);
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general.sex' => 'woman']]);
		(new Search\SuitedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->database
		))->seek();
		Assert::same(
			[['evolution_id' => 3]],
			(new Storage\NativeQuery($this->database, 'SELECT evolution_id FROM soulmates'))->rows()
		);
	}

	public function testMatchingHairColorsWithSimilar() {
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $demand] = (new Misc\SamplePostgresData($this->database, 'nullable_demand', ['seeker_id' => $seeker, 'general' => ['sex' => 'woman'], 'hair' => ['color_id' => 8]]))->try();
		$this->elasticsearch->index($params + ['body' => ['id' => 1, 'general.sex' => 'woman', 'hair.color_id' => 8]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general.sex' => 'woman', 'hair.color_id' => 12]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general.sex' => 'woman', 'hair.color_id' => 2]]);
		(new Search\SuitedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->database
		))->seek();
		$soulmates = (new Storage\NativeQuery($this->database, 'SELECT * FROM soulmates ORDER BY evolution_id'))->rows();
		Assert::count(3, $soulmates);
		Assert::true($soulmates[0]['score'] > $soulmates[1]['score']);
		Assert::true($soulmates[1]['score'] > $soulmates[2]['score']);
	}

	public function testBoostingLastnameOverFirstname() {
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $demand] = (new Misc\SamplePostgresData(
			$this->database,
			'nullable_demand',
			['seeker_id' => $seeker, 'general' => ['sex' => 'man', 'firstname' => 'Dominik', 'lastname' => 'Klapuch']]
		))->try();
		$this->elasticsearch->index($params + ['body' => ['id' => 1, 'general.sex' => 'man', 'general.lastname' => 'Klapuch']]);
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general.sex' => 'man', 'general.firstname' => 'Dominik']]);
		(new Search\SuitedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->database
		))->seek();
		$soulmates = (new Storage\NativeQuery($this->database, 'SELECT * FROM soulmates ORDER BY evolution_id'))->rows();
		Assert::count(2, $soulmates);
		Assert::true($soulmates[0]['score'] > $soulmates[1]['score']);
	}

	public function testNotMatchingColorButMatchDifference() {
		static $params = [
			'refresh' => true,
			'index' => 'relationships',
			'type' => 'evolutions',
		];
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $demand] = (new Misc\SamplePostgresData(
			$this->database,
			'nullable_demand',
			['seeker_id' => $seeker, 'general' => ['sex' => 'man'], 'left_eye' => ['color_id' => 8], 'right_eye' => ['color_id' => 10]]
		))->try();
		$this->elasticsearch->index($params + ['body' => ['id' => 1, 'general.sex' => 'man', 'right_eye.color_id' => 10, 'left_eye.color_id' => 8]]);
		$this->elasticsearch->index($params + ['body' => ['id' => 2, 'general.sex' => 'man', 'right_eye.color_id' => 3, 'left_eye.color_id' => 4]]); // diff
		$this->elasticsearch->index($params + ['body' => ['id' => 3, 'general.sex' => 'man', 'right_eye.color_id' => 5, 'left_eye.color_id' => 5]]);
		(new Search\SuitedSoulmates(
			$demand,
			$this->elasticsearch,
			$this->database
		))->seek();
		$soulmates = (new Storage\NativeQuery($this->database, 'SELECT * FROM soulmates ORDER BY evolution_id'))->rows();
		Assert::count(2, $soulmates);
		Assert::true($soulmates[0]['score'] > $soulmates[1]['score']);
	}
}

(new SuitedSoulmatesTest())->run();
