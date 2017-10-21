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
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri\FakeUri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class Put extends \Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleDemand($this->database))->try();
		$demand = json_decode(
			(new V1\Demand\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../Misc/demand.json')
					)
				),
				new FakeUri('/', 'v1/demands', []),
				$this->database,
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::null($demand);
		Assert::same(204, http_response_code());
	}

	public function testErrorOnBadInput() {
		(new Misc\SampleDemand($this->database))->try();
		$demand = json_decode(
			(new V1\Demand\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						'{"name":"bar"}'
					)
				),
				new FakeUri('/', 'v1/demands', []),
				$this->database,
				$this->redis
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'The property general is required'], $demand);
		Assert::same(400, http_response_code());
	}
}

(new Put())->run();