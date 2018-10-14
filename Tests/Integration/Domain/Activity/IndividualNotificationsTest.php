<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Activity;

use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Activity;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Dataset;
use Klapuch\Output;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class IndividualNotificationsTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testReceivingForSpecificSeeker(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker2]))->try();
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker]))->try();
		$notifications = (new Activity\IndividualNotifications(
			new Access\FakeSeeker((string) $seeker),
			$this->connection
		))->receive(new Dataset\FakeSelection([]));
		$notification = $notifications->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $notification->print(new Output\Json())->serialization());
		$notifications->next();
		$notification = $notifications->current();
		Assert::contains(sprintf('"seeker_id": %d', $seeker), $notification->print(new Output\Json())->serialization());
		$notifications->next();
		Assert::null($notifications->current());
	}

	public function testCountingForSpecificSeeker(): void {
		['id' => $seeker] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $seeker2] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker]))->try();
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker2]))->try();
		(new Misc\SamplePostgresData($this->connection, 'notification', ['seeker_id' => $seeker]))->try();
		Assert::same(
			2,
			(new Activity\IndividualNotifications(
				new Access\FakeSeeker((string) $seeker),
				$this->connection
			))->count(new Dataset\FakeSelection([]))
		);
	}
}

(new IndividualNotificationsTest())->run();
