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
}

(new StoredSoulmateTest())->run();