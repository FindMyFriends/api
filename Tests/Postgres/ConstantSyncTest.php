<?php
declare(strict_types = 1);

namespace FindMyFriends\Postgres;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class ConstantSyncTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testConstants(): void {
		Assert::same((new Access\Guest())->id(), (new Storage\NativeQuery($this->connection, 'SELECT constant.guest_id()::text'))->field());
	}
}

(new ConstantSyncTest())->run();
