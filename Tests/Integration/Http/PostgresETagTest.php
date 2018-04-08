<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Integration\Http;

use FindMyFriends\Http;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Klapuch\Uri;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class PostgresETagTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testStoredAsHexFormat() {
		$eTag = new Http\PostgresETag($this->database, new Uri\FakeUri(null, '/v1/demands/1'));
		$eTag->set(new \stdClass());
		Assert::match('"%h%"', $eTag->get());
	}

	public function testSameClassesWithSameTag() {
		$eTag = new Http\PostgresETag($this->database, new Uri\FakeUri(null, '/v1/demands/1'));
		$eTag->set(new \stdClass());
		$first = $eTag->get();
		$eTag->set(new \stdClass());
		$second = $eTag->get();
		Assert::same($first, $second);
	}

	public function testUpdatingTag() {
		$eTag = new Http\PostgresETag($this->database, new Uri\FakeUri(null, '/v1/demands/1'));
		$eTag->set(new \SplQueue());
		$first = $eTag->get();
		$eTag->set(new \stdClass());
		$second = $eTag->get();
		Assert::notSame($first, $second);
	}

	public function testUpdatingWithRecordedDatetime() {
		$id = (new Storage\NativeQuery(
			$this->database,
			'INSERT INTO http.etags (entity, tag, created_at) VALUES (?, ?, ?)
			RETURNING id',
			['/v1/demands/1', '123', '2010-01-01']
		))->field();
		$eTag = new Http\PostgresETag($this->database, new Uri\FakeUri(null, '/v1/demands/1'));
		$eTag->set(new \SplQueue());
		$current = (new Storage\NativeQuery(
			$this->database,
			'SELECT date_part(\'year\', created_at) AS created_at, tag FROM http.etags WHERE id = ?',
			[$id]
		))->row();
		Assert::notSame('2010', $current['created_at']);
		Assert::notSame('123', $current['tag']);
	}


	public function testAllowingAnonymousClasses() {
		$eTag = new Http\PostgresETag($this->database, new Uri\FakeUri(null, '/v1/demands/1'));
		Assert::noError(function() use ($eTag) {
			$eTag->set(new class () {

			});
		});
	}

	public function testCheckingExistence() {
		$eTag = new Http\PostgresETag($this->database, new Uri\FakeUri(null, '/v1/demands/1'));
		Assert::false($eTag->exists());
		$eTag->set(new \stdClass());
		Assert::true($eTag->exists());
	}

	public function testCaseInsensitiveEntities() {
		$lower = new Http\PostgresETag($this->database, new Uri\FakeUri(null, '/v1/demands/1'));
		$upper = new Http\PostgresETag($this->database, new Uri\FakeUri(null, '/V1/DEMANDS/1'));
		$lower->set(new \stdClass());
		Assert::true($upper->exists());
		Assert::same($lower->get(), $upper->get());
	}
}

(new PostgresETagTest())->run();
