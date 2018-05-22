<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\V1\Evolutions;

use FindMyFriends\Domain\Access;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Hashids\Hashids;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$response = (new V1\Evolutions\Get(
			new Hashids(),
			new Uri\FakeUri('/', 'v1/evolutions', []),
			$this->database,
			new Access\FakeSeeker((string) $seeker, ['role' => 'member']),
			new Http\FakeRole(true)
		))->response(['page' => 1, 'per_page' => 10]);
		$demands = json_decode($response->body()->serialization());
		Assert::count(2, $demands);
		(new Misc\SchemaAssertion(
			$demands,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Evolution/schema/get.json')
		))->assert();
	}
}

(new GetTest())->run();
