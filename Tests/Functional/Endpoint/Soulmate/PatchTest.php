<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Soulmate;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PatchTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand]))->try();
		$response = (new Endpoint\Soulmate\Patch(
			new Application\FakeRequest(
				new Output\FakeFormat(json_encode(['is_correct' => false]))
			),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$soulmate = json_decode($response->body()->serialization(), true);
		Assert::null($soulmate);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test400OnBadInput(): void {
		Assert::exception(function() {
			(new Endpoint\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['foo' => false]))
				),
				$this->connection,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property is_correct is required');
	}

	public function test404OnNotExisting(): void {
		Assert::exception(function() {
			(new Endpoint\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['is_correct' => false]))
				),
				$this->connection,
				new Access\FakeSeeker('666')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Soulmate does not exist', HTTP_NOT_FOUND);
	}

	public function test403OnForeign(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $seeker]))->try();
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand]))->try();
		Assert::exception(function() use ($id) {
			(new Endpoint\Soulmate\Patch(
				new Application\FakeRequest(
					new Output\FakeFormat(json_encode(['is_correct' => false]))
				),
				$this->connection,
				new Access\FakeSeeker('666')
			))->response(['id' => $id]);
		}, \UnexpectedValueException::class, 'This is not your soulmate', HTTP_FORBIDDEN);
	}

	public function test400OnEmptyBody(): void {
		Assert::exception(function() {
			(new Endpoint\Soulmate\Patch(
				new Application\FakeRequest(new Output\FakeFormat('{}')),
				$this->connection,
				new Access\FakeSeeker('1')
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property is_correct is required');
	}
}

(new PatchTest())->run();
