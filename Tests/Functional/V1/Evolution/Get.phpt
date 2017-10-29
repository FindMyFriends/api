<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Evolution;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class Get extends \Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => 1]))->try();
		$evolution = json_decode(
			(new V1\Evolution\Get(
				new Uri\FakeUri('/', sprintf('v1/evolutions/%d', $id), []),
				$this->database,
				new Access\FakeUser(null, ['role' => 'member']),
				$this->redis
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
				new Uri\FakeUri('/', sprintf('v1/evolutions/%d', 1), []),
				$this->database,
				new Access\FakeUser(null, ['role' => 'member']),
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Evolution 1 does not exist'], $evolution);
		Assert::same(404, http_response_code());
	}
}

(new Get())->run();