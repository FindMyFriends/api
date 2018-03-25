<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class StoredSoulmateTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testById() {
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES (?, ?, 20)',
			[(new Misc\SampleDemand($this->database))->try()['id'], (new Misc\SampleEvolution($this->database))->try()['id']]
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES (?, ?, 30)',
			[(new Misc\SampleDemand($this->database))->try()['id'], (new Misc\SampleEvolution($this->database))->try()['id']]
		))->execute();
		$soulmate = json_decode(
			(new Search\StoredSoulmate(
				2,
				$this->database
			))->print(new Output\Json())->serialization(),
			true
		);
		Assert::same(2, $soulmate['evolution_id']);
		Assert::same(2, $soulmate['demand_id']);
		Assert::same(3, $soulmate['seeker_id']);
	}

	public function testClarification() {
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'soulmate', ['is_correct' => true]))->try();
		(new Search\StoredSoulmate(
			$id,
			$this->database
		))->clarify(['is_correct' => false]);
		Assert::false((new Storage\TypedQuery($this->database, 'SELECT is_correct FROM soulmates'))->field());
	}
}

(new StoredSoulmateTest())->run();