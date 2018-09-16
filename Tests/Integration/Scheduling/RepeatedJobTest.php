<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Scheduling;

use FindMyFriends\Scheduling;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class RepeatedJobTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testFirstJobFulfillAlways() {
		ob_start();
		(new Scheduling\RepeatedJob(
			new Scheduling\FakeJob(static function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10H',
			$this->database
		))->fulfill();
		Assert::same('OK', ob_get_clean());
	}

	public function testIgnoringProcessingOnNotReady() {
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO log.cron_jobs(name, marked_at, status) VALUES (?, now(), ?)',
			['FakeJob', 'processing']
		))->execute();
		ob_start();
		(new Scheduling\RepeatedJob(
			new Scheduling\FakeJob(static function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10H',
			$this->database
		))->fulfill();
		Assert::same('', ob_get_clean());
	}

	public function testFulfillingForReadyOne() {
		(new Storage\NativeQuery(
			$this->database,
			"INSERT INTO log.cron_jobs(name, marked_at, status) VALUES (?, now() - interval '12 MINUTE', ?)",
			['FakeJob', 'succeed']
		))->execute();
		ob_start();
		(new Scheduling\RepeatedJob(
			new Scheduling\FakeJob(static function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10M',
			$this->database
		))->fulfill();
		Assert::same('OK', ob_get_clean());
	}

	public function testRunningByLastSuccess() {
		(new Storage\NativeQuery(
			$this->database,
			"INSERT INTO log.cron_jobs(name, marked_at, status) VALUES (?, now() - interval '12 MINUTE', ?)",
			['FakeJob', 'processing']
		))->execute();
		(new Storage\NativeQuery(
			$this->database,
			'INSERT INTO log.cron_jobs(name, marked_at, status) VALUES (?, now(), ?)',
			['FakeJob', 'succeed']
		))->execute();
		ob_start();
		(new Scheduling\RepeatedJob(
			new Scheduling\FakeJob(static function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10M',
			$this->database
		))->fulfill();
		Assert::same('', ob_get_clean());
	}
}

(new RepeatedJobTest())->run();
