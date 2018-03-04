<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc\SampleDemand;
use FindMyFriends\Misc\SampleEvolution;
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
			[(new SampleDemand($this->database))->try()['id'], (new SampleEvolution($this->database))->try()['id']]
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO soulmates (demand_id, evolution_id, score) VALUES (?, ?, 30)',
			[(new SampleDemand($this->database))->try()['id'], (new SampleEvolution($this->database))->try()['id']]
		))->execute();
		Assert::equal(
			[
				'id' => 2,
				'new' => true,
				'evolution_id' => 2,
				'demand_id' => 2,
				'position' => 1,
				'seeker_id' => 3,
			],
			json_decode(
				(new Search\StoredSoulmate(
					2,
					$this->database
				))->print(new Output\Json())->serialization(),
				true
			)
		);
	}
}

(new StoredSoulmateTest())->run();