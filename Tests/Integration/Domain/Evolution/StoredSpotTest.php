<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;

require __DIR__ . '/../../../bootstrap.php';

final class StoredSpotTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testForgettingByLocation() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $change] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->database, 'spot'))->try();
		(new Misc\SamplePostgresData($this->database, 'evolution_spot', ['evolution_id' => $change, 'spot_id' => $spot]))->try();
		(new Misc\TableCount($this->database, 'evolution_spots', 1))->assert();
		(new Misc\TableCount($this->database, 'spots', 2))->assert();
		(new Evolution\StoredSpot(
			$spot,
			$this->database
		))->forget();
		(new Misc\TableCount($this->database, 'evolution_spots', 0))->assert();
		(new Misc\TableCount($this->database, 'spots', 2))->assert();
	}
}

(new StoredSpotTest())->run();
