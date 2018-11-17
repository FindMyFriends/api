<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Activity;

use FindMyFriends\Domain\Activity;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class StoredNotificationTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testMarkingAsSeenAndUnseen(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'notification', ['seen' => false]))->try();
		Assert::false((new Storage\TypedQuery($this->connection, 'SELECT seen FROM notifications WHERE id = ?', [$id]))->field());
		(new Activity\StoredNotification($id, $this->connection))->seen();
		Assert::true((new Storage\TypedQuery($this->connection, 'SELECT seen FROM notifications WHERE id = ?', [$id]))->field());
		(new Activity\StoredNotification($id, $this->connection))->unseen();
		Assert::false((new Storage\TypedQuery($this->connection, 'SELECT seen FROM notifications WHERE id = ?', [$id]))->field());
	}
}

(new StoredNotificationTest())->run();
