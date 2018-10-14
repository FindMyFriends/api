<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Notifications;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class GetTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		$seeker = current((new Misc\SamplePostgresData($this->connection, 'seeker'))->try());
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Notifications\Get(
			new Uri\FakeUri('/', 'notifications', []),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		Assert::count(2, json_decode($response->body()->serialization()));
		(new Misc\SchemaAssertion(
			json_decode($response->body()->serialization()),
			(new \SplFileInfo(Endpoint\Notifications\Get::SCHEMA))
		))->assert();
	}

	public function testSuccessOnNoNotifications(): void {
		$response = (new Endpoint\Notifications\Get(
			new Uri\FakeUri('/', 'notifications', []),
			$this->connection,
			new Access\FakeSeeker('1')
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		Assert::count(0, json_decode($response->body()->serialization()));
	}

	public function testIncludedCountHeader(): void {
		$headers = (new Endpoint\Notifications\Get(
			new Uri\FakeUri('/', 'notifications', []),
			$this->connection,
			new Access\FakeSeeker('1')
		))->response(['page' => 1, 'per_page' => 10, 'sort' => ''])->headers();
		Assert::same(0, $headers['X-Total-Count']);
	}
}

(new GetTest())->run();
