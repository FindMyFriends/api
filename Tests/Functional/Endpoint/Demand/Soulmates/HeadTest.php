<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Demand\Soulmates;

use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../../bootstrap.php';

final class HeadTest extends Tester\TestCase {
	use TestCase\Page;

	public function testEmptyResponse() {
		$seeker = (string) current((new Misc\SamplePostgresData($this->database, 'seeker'))->try());
		['id' => $demand1] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand1]))->try();
		(new Misc\SamplePostgresData($this->database, 'soulmate_request', ['demand_id' => $demand1]))->try();
		$response = (new Endpoint\Demand\Soulmates\Head(
			new Uri\FakeUri('/', 'soulmates', []),
			$this->database,
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => $demand1]);
		Assert::null(json_decode($response->body()->serialization()));
	}

	public function testNeededHeaders() {
		$seeker = (string) current((new Misc\SamplePostgresData($this->database, 'seeker'))->try());
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$headers = (new Endpoint\Demand\Soulmates\Head(
			new Uri\FakeUri('/', 'soulmates', []),
			$this->database,
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => $demand])->headers();
		Assert::count(3, $headers);
		Assert::same(0, $headers['X-Total-Count']);
		Assert::same('text/plain', $headers['Content-Type']);
		Assert::true(isset($headers['Link']));
	}
}

(new HeadTest())->run();
