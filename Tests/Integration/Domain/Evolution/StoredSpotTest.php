<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Evolution;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class StoredSpotTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testForgettingBySpot() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $change] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $spot] = (new Misc\SamplePostgresData($this->connection, 'spot'))->try();
		(new Misc\SamplePostgresData($this->connection, 'evolution_spot', ['evolution_id' => $change, 'spot_id' => $spot]))->try();
		(new Misc\TableCount($this->connection, 'evolution_spots', 1))->assert();
		(new Misc\TableCount($this->connection, 'spots', 2))->assert();
		(new Evolution\StoredSpot(
			$spot,
			$this->connection
		))->forget();
		(new Misc\TableCount($this->connection, 'evolution_spots', 0))->assert();
		(new Misc\TableCount($this->connection, 'spots', 2))->assert();
	}
}

(new StoredSpotTest())->run();
