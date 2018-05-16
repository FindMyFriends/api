<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 * @httpCode any
 */
namespace FindMyFriends\Functional\V1\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use FindMyFriends\V1;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class DeleteTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		(new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$this->elasticsearch->index(['index' => 'relationships', 'type' => 'evolutions', 'id' => $id, 'body' => []]);
		$response = (new V1\Evolution\Delete(
			$this->database,
			$this->elasticsearch,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$evolution = json_decode($response->body()->serialization(), true);
		Assert::null($evolution);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test404OnNotExisting() {
		$response = (new V1\Evolution\Delete(
			$this->database,
			$this->elasticsearch,
			new Access\FakeSeeker()
		))->response(['id' => 1]);
		$evolution = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'Evolution change does not exist'], $evolution);
		Assert::same(HTTP_NOT_FOUND, $response->status());
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database))->try();
		$response = (new V1\Evolution\Delete(
			$this->database,
			$this->elasticsearch,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$evolution = json_decode($response->body()->serialization(), true);
		Assert::same(['message' => 'You are not permitted to see this evolution change.'], $evolution);
		Assert::same(HTTP_FORBIDDEN, $response->status());
	}
}

(new DeleteTest())->run();
