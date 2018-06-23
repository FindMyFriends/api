<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Functional\Endpoint\Evolution;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class PutTest extends Tester\TestCase {
	use TestCase\Page;

	public function testSuccessfulResponse() {
		(new Misc\SampleEvolution($this->database))->try();
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database, ['seeker_id' => $seeker]))->try();
		$this->elasticsearch->index(['index' => 'relationships', 'type' => 'evolutions', 'id' => $id, 'body' => []]);
		$response = (new Endpoint\Evolution\Put(
			new Application\FakeRequest(
				new Output\FakeFormat(
					file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/put.json')
				)
			),
			new Uri\FakeUri('/', 'evolutions/1', []),
			$this->database,
			$this->elasticsearch,
			new Access\FakeSeeker((string) $seeker)
		))->response(['id' => $id]);
		$evolution = json_decode($response->body()->serialization(), true);
		Assert::null($evolution);
		Assert::same(HTTP_NO_CONTENT, $response->status());
	}

	public function test400OnBadInput() {
		Assert::exception(function () {
			(new Endpoint\Evolution\Put(
				new Application\FakeRequest(new Output\FakeFormat('{"name":"bar"}')),
				new Uri\FakeUri('/', 'evolutions/1', []),
				$this->database,
				$this->elasticsearch,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'The property general is required');
	}

	public function test404OnNotExisting() {
		Assert::exception(function () {
			(new Endpoint\Evolution\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/put.json')
					)
				),
				new Uri\FakeUri('/', 'evolutions/1', []),
				$this->database,
				$this->elasticsearch,
				new Access\FakeSeeker()
			))->response(['id' => 1]);
		}, \UnexpectedValueException::class, 'Evolution change does not exist', HTTP_NOT_FOUND);
	}

	public function test403OnForeign() {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->database, 'seeker'))->try();
		['id' => $id] = (new Misc\SampleEvolution($this->database))->try();
		Assert::exception(function () use ($seeker, $id) {
			(new Endpoint\Evolution\Put(
				new Application\FakeRequest(
					new Output\FakeFormat(
						file_get_contents(__DIR__ . '/../../../fixtures/samples/evolution/put.json')
					)
				),
				new Uri\FakeUri('/', 'evolutions/1', []),
				$this->database,
				$this->elasticsearch,
				new Access\FakeSeeker((string) $seeker)
			))->response(['id' => $id]);
		}, \UnexpectedValueException::class, 'You are not permitted to see this evolution change.', HTTP_FORBIDDEN);
	}
}

(new PutTest())->run();
