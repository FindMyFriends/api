<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Demands;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri\FakeUri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PostTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		$demand = json_decode(
			(new V1\Demands\Post(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/demand/post.json')
					)
				),
				new FakeUri('/', 'v1/demands', []),
				$this->database,
				new Access\FakeUser((string) $seeker, ['role' => 'guest'])
			))->template([])->render(),
			true
		);
		Assert::null($demand);
		Assert::same(HTTP_CREATED, http_response_code());
	}

	public function test400OnBadInput() {
		$demand = json_decode(
			(new V1\Demands\Post(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new FakeUri('/', 'v1/demands', []),
				$this->database,
				new Access\FakeUser('1', ['role' => 'guest'])
			))->template([])->render(),
			true
		);
		Assert::same(['message' => 'The property general is required'], $demand);
		Assert::same(HTTP_BAD_REQUEST, http_response_code());
	}
}

(new PostTest())->run();