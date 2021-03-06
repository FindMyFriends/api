<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Http;

use FindMyFriends\Http;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Klapuch\Uri;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class PostgresETagTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testStoredAsHexFormat(): void {
		$eTag = new Http\PostgresETag($this->connection, new Uri\FakeUri(null, '/demands/1'));
		$eTag->set(new \stdClass());
		Assert::match('"%h%"', $eTag->get());
	}

	public function testSameClassesWithSameTag(): void {
		$eTag = new Http\PostgresETag($this->connection, new Uri\FakeUri(null, '/demands/1'));
		$eTag->set(new \stdClass());
		$first = $eTag->get();
		$eTag->set(new \stdClass());
		$second = $eTag->get();
		Assert::same($first, $second);
	}

	public function testUpdatingTag(): void {
		$eTag = new Http\PostgresETag($this->connection, new Uri\FakeUri(null, '/demands/1'));
		$eTag->set(new \SplQueue());
		$first = $eTag->get();
		$eTag->set(new \stdClass());
		$second = $eTag->get();
		Assert::notSame($first, $second);
	}

	public function testUpdatingWithRecordedDatetime(): void {
		$id = (new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO etags (entity, tag, created_at) VALUES (?, ?, ?)
			RETURNING id',
			['/demands/1', '123', '2010-01-01']
		))->field();
		$eTag = new Http\PostgresETag($this->connection, new Uri\FakeUri(null, '/demands/1'));
		$eTag->set(new \SplQueue());
		$current = (new Storage\NativeQuery(
			$this->connection,
			'SELECT date_part(\'year\', created_at) AS created_at, tag FROM etags WHERE id = ?',
			[$id]
		))->row();
		Assert::notSame('2010', $current['created_at']);
		Assert::notSame('123', $current['tag']);
	}

	public function testAllowingAnonymousClasses(): void {
		$eTag = new Http\PostgresETag($this->connection, new Uri\FakeUri(null, '/demands/1'));
		Assert::noError(static function() use ($eTag) {
			$eTag->set(new class () {

			});
		});
	}

	public function testCheckingExistence(): void {
		$eTag = new Http\PostgresETag($this->connection, new Uri\FakeUri(null, '/demands/1'));
		Assert::false($eTag->exists());
		$eTag->set(new \stdClass());
		Assert::true($eTag->exists());
	}

	public function testCaseInsensitiveEntities(): void {
		$lower = new Http\PostgresETag($this->connection, new Uri\FakeUri(null, '/demands/1'));
		$upper = new Http\PostgresETag($this->connection, new Uri\FakeUri(null, '/DEMANDS/1'));
		$lower->set(new \stdClass());
		Assert::true($upper->exists());
		Assert::same($lower->get(), $upper->get());
	}
}

(new PostgresETagTest())->run();
