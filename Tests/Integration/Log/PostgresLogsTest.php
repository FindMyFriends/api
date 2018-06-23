<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Log;

use FindMyFriends\Log;
use FindMyFriends\TestCase;
use Klapuch\Log\CurrentEnvironment;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PostgresLogsTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testInsertingNewLog() {
		(new Log\PostgresLogs(
			$this->database
		))->put(new \RuntimeException('Ooops'), new CurrentEnvironment());
		$logs = (new Storage\NativeQuery($this->database, 'SELECT * FROM log.logs'))->rows();
		Assert::count(1, $logs);
	}
}

(new PostgresLogsTest())->run();
