<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\V1\Demand\Soulmates;

use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
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
		$response = (new V1\Demand\Soulmates\Head(
			new Uri\FakeUri('/', 'v1/soulmates', []),
			$this->database,
			new Http\FakeRole(true),
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => $demand1]);
		Assert::null(json_decode($response->body()->serialization()));
	}

	public function testNeededHeaders() {
		$seeker = (string) current((new Misc\SamplePostgresData($this->database, 'seeker'))->try());
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$headers = (new V1\Demand\Soulmates\Head(
			new Uri\FakeUri('/', 'v1/soulmates', []),
			$this->database,
			new Http\FakeRole(true),
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => $demand])->headers();
		Assert::count(3, $headers);
		Assert::same(0, $headers['X-Total-Count']);
		Assert::same('text/plain', $headers['Content-Type']);
		Assert::true(isset($headers['Link']));
	}

	public function testErrorInJsonFormat() {
		$response = (new V1\Demand\Soulmates\Head(
			new Uri\FakeUri('/', 'v1/soulmates', []),
			$this->database,
			new Http\FakeRole(false),
			$this->elasticsearch
		))->response(['page' => 1, 'per_page' => 10, 'demand_id' => 1]);
		Assert::same(HTTP_FORBIDDEN, $response->status());
		$headers = $response->headers();
		Assert::count(3, $headers);
		Assert::same('application/json; charset=utf8', $headers['Content-Type']);
	}
}

(new HeadTest())->run();
