<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Evolution;

use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class GetTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$evolution = json_decode(
			(new V1\Evolution\Get(
				new Uri\FakeUri('/', 'v1/evolutions/1', []),
				$this->database,
				new Http\FakeRole(true)
			))->template(['id' => $id])->render()
		);
		Assert::same($id, $evolution->id);
		(new Misc\SchemaAssertion(
			$evolution,
			new \SplFileInfo(__DIR__ . '/../../../../App/V1/Evolution/schema/get.json')
		))->assert();
	}

	public function test404ForNotExisting() {
		$evolution = json_decode(
			(new V1\Evolution\Get(
				new Uri\FakeUri('/', 'v1/evolutions/1', []),
				$this->database,
				new Http\FakeRole(true)
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Evolution change 1 does not exist'], $evolution);
		Assert::same(HTTP_NOT_FOUND, http_response_code());
	}
}

(new GetTest())->run();