<?php
declare(strict_types = 1);

namespace FindMyFriends\Postgres;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

/**
 * @testCase
 * @phpVersion > 7.2
 */
final class Test extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testPostgres() {
		(new class(new \SplFileInfo(__DIR__), $this->database) implements Misc\Assertion {
			private const PATTERN = '~\.sql$~i';

			/** @var \SplFileInfo */
			private $source;

			/** @var \PDO */
			private $database;

			public function __construct(\SplFileInfo $source, \PDO $database) {
				$this->source = $source;
				$this->database = $database;
			}

			public function assert(): void {
				foreach ($this->files($this->source) as $file) {
					$this->import($file);
					$this->test($file);
				}
			}

			private function test(\SplFileInfo $file): void {
				foreach ($this->functions($file) as $function) {
					$this->database->beginTransaction();
					try {
						$this->database->exec(sprintf('SELECT %s()', $function));
					} catch (\PDOException $e) {
						Assert::fail((new \FindMyFriends\Postgres\PlestException($e, $file))->getMessage());
					} finally {
						$this->database->rollBack();
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
					$this->database->exec(file_get_contents($file->getPathname()));
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
