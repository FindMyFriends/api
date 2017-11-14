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
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class Delete extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$evolution = json_decode(
			(new V1\Evolution\Delete($this->database))->template(['id' => $id])->render(),
			true
		);
		Assert::null($evolution);
		Assert::same(204, http_response_code());
	}

	public function test404OnNotExisting() {
		$evolution = json_decode(
			(new V1\Evolution\Delete($this->database))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Evolution change 1 does not exist'], $evolution);
		Assert::same(404, http_response_code());
	}
}

(new Delete())->run();