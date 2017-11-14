<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Demand;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class Delete extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleDemand($this->database))->try();
		['id' => $seeker] = (new Misc\SampleSeeker($this->database))->try();
		['id' => $id] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		$demand = json_decode(
			(new V1\Demand\Delete($this->database))->template(['id' => $id])->render(),
			true
		);
		Assert::null($demand);
		Assert::same(HTTP_NO_CONTENT, http_response_code());
	}

	public function test404OnNotExisting() {
		$demand = json_decode(
			(new V1\Demand\Delete($this->database))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Demand 1 does not exist'], $demand);
		Assert::same(404, http_response_code());
	}
}

(new Delete())->run();