<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\V1\Soulmate;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
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
		$response = (new V1\Soulmate\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['is_correct' => false]))
			),
			$this->database,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$soulmate = json_decode($response->body()->serialization(), true);
		Assert::null($soulmate);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test400OnBadInput() {
		$response = (new V1\Soulmate\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['foo' => false]))
			),
			$this->database,
			new Access\FakeSeeker((string) '1')
		))->response(['id' => 1]);
		$soulmate = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'The property foo is not defined and the definition does not allow additional properties'], $soulmate);
		Assert::same(HTTP_BAD_REQUEST, $response->status());
	}

	public function test404OnNotExisting() {
		$response = (new V1\Soulmate\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['is_correct' => false]))
			),
			$this->database,
			new Access\FakeSeeker('666')
		))->response(['id' => 1]);
		$soulmate = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Soulmate does not exist'], $soulmate);
		Assert::same(HTTP_NOT_FOUND, $response->status());
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand]))->try();
		$response = (new V1\Soulmate\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['is_correct' => false]))
			),
			$this->database,
			new Access\FakeSeeker('666')
		))->response(['id' => $id]);
		$soulmate = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'This is not your soulmate'], $soulmate);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}
}

(new PatchTest())->run();
