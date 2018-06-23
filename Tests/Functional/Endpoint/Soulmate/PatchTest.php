<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Soulmate;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
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
		$response = (new Endpoint\Soulmate\Patch(
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
		Assert::exception(function() {
			(new Endpoint\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['foo' => false]))
				),
				$this->database,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property is_correct is required');
	}

	public function test404OnNotExisting() {
		Assert::exception(function() {
			(new Endpoint\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['is_correct' => false]))
				),
				$this->database,
				new Access\FakeSeeker('666')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Soulmate does not exist', HTTP_NOT_FOUND);
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->database, ['seeker_id' => $seeker]))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->database, 'soulmate', ['demand_id' => $demand]))->try();
		Assert::exception(function() use ($id) {
			(new Endpoint\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['is_correct' => false]))
				),
				$this->database,
				new Access\FakeSeeker('666')
			))->response(['id' => $id]);
		}, \UnexpectedValueException::class, 'This is not your soulmate', HTTP_FORBIDDEN);
	}

	public function test400OnEmptyBody() {
		Assert::exception(function() {
			(new Endpoint\Soulmate\Patch(
				new Application\FakeRequest(new Output\FakeFormat('')),
				$this->database,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property is_correct is required');
	}
}

(new PatchTest())->run();
