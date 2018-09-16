<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Log;

use FindMyFriends;
use Klapuch\Log;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class FilesystemLogsTest extends Tester\TestCase {
	private const LOGS = __DIR__ . '/../../temp/logs';

	public function setUp() {
		parent::setUp();
		Tester\Helpers::purge(self::LOGS);
	}

	public function testFormat() {
		(new FindMyFriends\Log\FilesystemLogs(
			new \SplFileInfo(self::LOGS . '/a.txt')
		))->put(
			new \RuntimeException('foo'),
			new Log\FakeEnvironment()
		);
		Assert::same(
			preg_replace('~^\[.+\]~', '[2010-01-01 01:01]', file_get_contents(self::LOGS . '/a.txt')),
			file_get_contents(__DIR__ . '/snaphosts/FilesystemLogs.format.txt')
		);
	}
}

(new FilesystemLogsTest())->run();
