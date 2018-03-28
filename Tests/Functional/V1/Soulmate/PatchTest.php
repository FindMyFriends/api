<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Soulmate;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PatchTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand]))->try();
		$response = json_decode(
			(new V1\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['is_correct' => false]))
				),
				$this->database,
				new Access\FakeUser((string) $seeker)
			))->template(['id' => $id])->render(),
			true
		);
		Assert::null($response);
		Assert::same(HTTP_NO_CONTENT, http_response_code());
	}

	public function test400OnBadInput() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand]))->try();
		$response = json_decode(
			(new V1\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['foo' => false]))
				),
				$this->database,
				new Access\FakeUser((string) $seeker)
			))->template(['id' => $id])->render(),
			true
		);
		Assert::same(['message' => 'The property foo is not defined and the definition does not allow additional properties'], $response);
		Assert::same(HTTP_BAD_REQUEST, http_response_code());
	}

	public function test404OnUnknown() {
		$response = json_decode(
			(new V1\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['is_correct' => false]))
				),
				$this->database,
				new Access\FakeUser('666')
			))->template(['id' => 1])->render(),
			true
		);
		Assert::same(['message' => 'Soulmate does not exist'], $response);
		Assert::same(HTTP_NOT_FOUND, http_response_code());
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand]))->try();
		$response = json_decode(
			(new V1\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['is_correct' => false]))
				),
				$this->database,
				new Access\FakeUser('666')
			))->template(['id' => $id])->render(),
			true
		);
		Assert::same(['message' => 'This is not your soulmate'], $response);
		Assert::same(HTTP_FORBIDDEN, http_response_code());
	}
}

(new PatchTest())->run();