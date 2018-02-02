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
				$this->import(file_get_contents($test->getPathname()), $test->getBasename('.sql'));
				try {
					return $this->database->query('SELECT * FROM unit_tests.begin()')->fetch();
				} finally {
					$this->database->rollBack();
				}
			}

			private function import(string $content, string $name): void {
				$this->database->exec($content);
				$this->rename($content, $name);
			}

			private function rename(string $content, string $name): void {
				preg_match_all(
					'~CREATE FUNCTION unit_tests\.(?P<functions>\w+)\(\)~',
					$content,
					$matches
				);
				foreach ($matches['functions'] as $function) {
					$this->database->exec(
						sprintf(
							'ALTER FUNCTION unit_tests.%1$s() RENAME TO __%2$s__%1$s',
							$function,
							$name
						)
					);
				}
			}
		})->assert();
	}
}

(new Test)->run();