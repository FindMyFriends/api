<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Log;

use FindMyFriends;
use FindMyFriends\TestCase;
use Klapuch\Log;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class FilesystemLogsTest extends TestCase\Runtime {
	private const LOGS = __DIR__ . '/../../temp/logs';

	public function setUp(): void {
		parent::setUp();
		Tester\Helpers::purge(self::LOGS);
	}

	public function testFormat(): void {
		(new FindMyFriends\Log\FilesystemLogs(
			new \SplFileInfo(self::LOGS . '/a.txt')
		))->put(
			new \RuntimeException('foo'),
			new Log\FakeEnvironment()
		);
		Assert::same(
			preg_replace('~^\[.+\]~', '[2010-01-01 01:01]', (string) file_get_contents(self::LOGS . '/a.txt')),
			file_get_contents(__DIR__ . '/snaphosts/FilesystemLogs.format.txt')
		);
	}
}

(new FilesystemLogsTest())->run();
