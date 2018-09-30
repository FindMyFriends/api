<?php
declare(strict_types = 1);

namespace FindMyFriends\Postgres;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 */
final class Test extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testPostgres() {
		(new class(new \SplFileInfo(__DIR__), $this->connection) implements Misc\Assertion {
			private const PATTERN = '~\.sql$~i';

			/** @var \SplFileInfo */
			private $source;

			/** @var \Klapuch\Storage\Connection */
			private $connection;

			public function __construct(\SplFileInfo $source, Storage\Connection $connection) {
				$this->source = $source;
				$this->connection = $connection;
			}

			public function assert(): void {
				foreach ($this->files($this->source) as $file) {
					$this->import($file);
					$this->test($file);
				}
			}

			private function test(\SplFileInfo $file): void {
				foreach ($this->functions($file) as $function) {
					$this->connection->exec('START TRANSACTION');
					try {
						$this->connection->exec(sprintf('SELECT %s()', $function));
					} catch (\PDOException $e) {
						Assert::fail((new \FindMyFriends\Postgres\PlestException($e, $file))->getMessage());
					} finally {
						$this->connection->exec('ROLLBACK TRANSACTION');
					}
				}
				Assert::true(true);
			}

			private function functions(\SplFileInfo $file): array {
				preg_match_all(
					'~^CREATE FUNCTION (?P<functions>tests\.\w+)\(\)~mi',
					file_get_contents($file->getPathname()),
					$matches
				);
				return $matches['functions'];
			}

			private function import(\SplFileInfo $file): void {
				try {
					$this->connection->exec(file_get_contents($file->getPathname()));
				} catch (\PDOException $e) {
					Assert::fail((new \FindMyFriends\Postgres\PlestException($e, $file))->getMessage());
				}
			}

			/**
			 * @param \SplFileInfo $source
			 * @return \SplFileInfo[]
			 */
			private function files(\SplFileInfo $source): \Iterator {
				return new \RegexIterator(
					new \RecursiveIteratorIterator(
						new \RecursiveDirectoryIterator(
							$source->getPathname()
						)
					),
					self::PATTERN
				);
			}
		})->assert();
	}
}

(new Test())->run();
