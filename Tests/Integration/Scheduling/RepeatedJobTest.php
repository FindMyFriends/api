<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Scheduling;

use FindMyFriends\Scheduling;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class RepeatedJobTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testFirstJobFulfillAlways(): void {
		ob_start();
		(new Scheduling\RepeatedJob(
			new Scheduling\FakeJob(static function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10H',
			$this->connection
		))->fulfill();
		Assert::same('OK', ob_get_clean());
	}

	public function testIgnoringProcessingOnNotReady(): void {
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO log.cron_jobs(name, marked_at, status) VALUES (?, now(), ?)',
			['FakeJob', 'processing']
		))->execute();
		ob_start();
		(new Scheduling\RepeatedJob(
			new Scheduling\FakeJob(static function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10H',
			$this->connection
		))->fulfill();
		Assert::same('', ob_get_clean());
	}

	public function testFulfillingForReadyOne(): void {
		(new Storage\NativeQuery(
			$this->connection,
			"INSERT INTO log.cron_jobs(name, marked_at, status) VALUES (?, now() - interval '12 MINUTE', ?)",
			['FakeJob', 'succeed']
		))->execute();
		ob_start();
		(new Scheduling\RepeatedJob(
			new Scheduling\FakeJob(static function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10M',
			$this->connection
		))->fulfill();
		Assert::same('OK', ob_get_clean());
	}

	public function testRunningByLastSuccess(): void {
		(new Storage\NativeQuery(
			$this->connection,
			"INSERT INTO log.cron_jobs(name, marked_at, status) VALUES (?, now() - interval '12 MINUTE', ?)",
			['FakeJob', 'processing']
		))->execute();
		(new Storage\NativeQuery(
			$this->connection,
			'INSERT INTO log.cron_jobs(name, marked_at, status) VALUES (?, now(), ?)',
			['FakeJob', 'succeed']
		))->execute();
		ob_start();
		(new Scheduling\RepeatedJob(
			new Scheduling\FakeJob(static function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10M',
			$this->connection
		))->fulfill();
		Assert::same('', ob_get_clean());
	}
}

(new RepeatedJobTest())->run();
