<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Interaction;

use FindMyFriends\Domain\Interaction;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class DemandSpotsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testTrackingForDemandChange() {
		['id' => $demand] = (new Misc\SampleDemand($this->database))->try();
		(new Interaction\DemandSpots(
			$demand,
			$this->database
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
		(new Misc\TableCount($this->database, 'spots', 1))->assert();
		(new Misc\TableCount($this->database, 'demand_spots', 1))->assert();
	}

	public function testDemandsForChange() {
		['id' => $demand1] = (new Misc\SampleDemand($this->database))->try();
		['id' => $demand2] = (new Misc\SampleDemand($this->database))->try();
		(new Misc\SamplePostgresData($this->database, 'demand_spot', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'demand_spot', ['demand_id' => $demand2]))->try();
		$spots = (new Interaction\DemandSpots(
			$demand1,
			$this->database
		))->history();
		$spot = $spots->current();
		Assert::contains(sprintf('"demand_id": %d', $demand1), $spot->print(new Output\Json())->serialization());
		$spots->next();
		Assert::null($spots->current());
	}
}

(new DemandSpotsTest())->run();
