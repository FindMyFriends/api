<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Postgres;

use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class Test extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testPostgres() {
		(new class(new \SplFileInfo(__DIR__), $this->database) implements Misc\Assertion {
			private const PATTERN = '~\.sql$~i';
			private $source;
			private $database;

			public function __construct(\SplFileInfo $source, \PDO $database) {
				$this->source = $source;
				$this->database = $database;
			}

			public function assert(): void {
				foreach ($this->tests($this->source) as $test) {
					$output = $this->output($test);
					Assert::same('Y', $output['result'], $output['message']);
				}
			}

			private function tests(\SplFileInfo $source): iterable {
				return new \RegexIterator(
					new \RecursiveIteratorIterator(
						new \RecursiveDirectoryIterator(
							$source->getPathname()
						)
					),
					self::PATTERN
				);
			}

			private function output(\SplFileInfo $test): array {
				$this->database->beginTransaction();
				$this->database->exec(file_get_contents($test->getPathname()));
				try {
					return $this->database->query('SELECT * FROM unit_tests.begin()')->fetch();
				} finally {
					$this->database->rollBack();
				}
			}
		})->assert();
	}
}

(new Test)->run();