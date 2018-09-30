<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class ChangeSpotsTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testTrackingForEvolutionChange(): void {
		['id' => $change] = (new Misc\SampleEvolution($this->connection))->try();
		(new Evolution\ChangeSpots(
			$change,
			$this->connection
		))->track(
			[
				'coordinates' => [
					'latitude' => 50.5,
					'longitude' => 50.3,
				],
				'met_at' => [
					'moment' => '2018-01-01 01:01:01',
					'timeline_side' => 'sooner',
					'approximation' => 'PT2H',
				],
			]
		);
		(new Misc\TableCount($this->connection, 'spots', 1))->assert();
		(new Misc\TableCount($this->connection, 'evolution_spots', 1))->assert();
	}

	public function testEvolutionsForChange(): void {
		['id' => $change1] = (new Misc\SampleEvolution($this->connection))->try();
		['id' => $change2] = (new Misc\SampleEvolution($this->connection))->try();
		(new Misc\SamplePostgresData($this->connection, 'evolution_spot', ['evolution_id' => $change1]))->try();
		(new Misc\SamplePostgresData($this->connection, 'evolution_spot', ['evolution_id' => $change2]))->try();
		$spots = (new Evolution\ChangeSpots(
			$change1,
			$this->connection
		))->history();
		$spot = $spots->current();
		Assert::contains(sprintf('"evolution_id": %d', $change1), $spot->print(new Output\Json())->serialization());
		$spots->next();
		Assert::null($spots->current());
	}
}

(new ChangeSpotsTest())->run();
