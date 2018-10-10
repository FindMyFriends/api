<?php
declare(strict_types = 1);

namespace FindMyFriends\Functional\Endpoint\Seekers\Me;

use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;

require __DIR__ . '/../../../../bootstrap.php';

/**
 * @testCase
 */
final class GetTest extends TestCase\Runtime {
	use TestCase\Page;

	public function testSuccessfulResponse(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SamplePostgresData(
			$this->connection,
			'seeker_contact',
			['seeker_id' => $id, 'facebook' => 'test_fb', 'instagram' => 'test_ig', 'phone_number' => null]
		))->try();
		(new Misc\SchemaAssertion(
			json_decode(
				(new Endpoint\Seekers\Me\Get(
					$this->connection,
					new Access\FakeSeeker((string) $id)
				))->response([])->body()->serialization()
			),
			new \SplFileInfo(Endpoint\Seekers\Me\Get::SCHEMA)
		))->assert();
	}
}

(new GetTest())->run();
