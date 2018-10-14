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
final class HeadTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testEmptyResponse(): void {
		$seeker = current((new Misc\SamplePostgresData($this->connection, 'seeker'))->try());
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker]))->try();
		$response = (new Endpoint\Notifications\Head(
			new Uri\FakeUri('/', 'notifications', []),
			$this->connection,
			new Access\FakeSeeker((string) $seeker)
		))->response(['page' => 1, 'per_page' => 10, 'sort' => '']);
		Assert::null(json_decode($response->body()->serialization()));
	}

	public function testNeededHeaders(): void {
		$headers = (new Endpoint\Notifications\Head(
			new Uri\FakeUri('/', 'notifications', []),
			$this->connection,
			new Access\FakeSeeker('1')
		))->response(['page' => 1, 'per_page' => 10, 'sort' => ''])->headers();
		Assert::count(3, $headers);
		Assert::same(0, $headers['X-Total-Count']);
		Assert::same('text/plain', $headers['Content-Type']);
		Assert::true(isset($headers['Link']));
	}
}

(new HeadTest())->run();
