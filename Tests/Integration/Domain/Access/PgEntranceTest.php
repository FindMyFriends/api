<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PgEntranceTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testSettingSeekerForEnterToDatabase(): void {
		$seeker = new Access\FakeSeeker('10');
		Assert::same($seeker, (new Access\PgEntrance(new Access\FakeEntrance($seeker), $this->connection))->enter([]));
		Assert::same(10, (new Storage\NativeQuery($this->connection, 'SELECT globals_get_seeker()'))->field());
	}

	public function testUnsetSeekerForExitInDatabase(): void {
		(new Storage\NativeQuery($this->connection, 'SELECT globals_set_seeker(10)'))->execute();
		Assert::same(10, (new Storage\NativeQuery($this->connection, 'SELECT globals_get_seeker()'))->field());
		$seeker = new Access\FakeSeeker('10');
		Assert::same($seeker, (new Access\PgEntrance(new Access\FakeEntrance($seeker), $this->connection))->exit());
		Assert::null((new Storage\NativeQuery($this->connection, 'SELECT globals_get_seeker()'))->field());
	}
}

(new PgEntranceTest())->run();
