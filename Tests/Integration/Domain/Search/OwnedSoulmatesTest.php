<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Search;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Klapuch\Internal\DecodedJson;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class OwnedSoulmatesTest extends TestCase\Runtime {
	use TestCase\Search;

	public function testOnlyByOwner(): void {
		['id' => $seeker1] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		$seeker = new Access\FakeSeeker((string) $seeker1);
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $seeker3] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand1] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker1]))->try();
		['id' => $demand2] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker3]))->try();
		['id' => $evolution1] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker2]))->try();
		['id' => $evolution2] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $seeker1]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate_request', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate_request', ['demand_id' => $demand2]))->try();
		['id' => $soulmate1] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand1, 'evolution_id' => $evolution1]))->try();
		['id' => $soulmate2] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand2, 'evolution_id' => $evolution2]))->try();
		$soulmates = new Search\OwnedSoulmates($seeker, $this->connection);
		Assert::same(2, $soulmates->count(new Dataset\EmptySelection()));
		$matches = $soulmates->matches(new Dataset\EmptySelection());
		Assert::same($soulmate1, (new DecodedJson($matches->current()->print(new Output\Json())->serialization()))->values()['id']);
		$matches->next();
		Assert::same($soulmate2, (new DecodedJson($matches->current()->print(new Output\Json())->serialization()))->values()['id']);
		$matches->next();
		Assert::null($matches->current());
	}
}

(new OwnedSoulmatesTest())->run();
