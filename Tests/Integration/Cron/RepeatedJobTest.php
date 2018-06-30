<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Cron;

use FindMyFriends\Cron;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class RepeatedJobTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testFirstJobFulfillAlways() {
		ob_start();
		(new Cron\RepeatedJob(
			new Cron\FakeJob(function () {
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
		(new Cron\RepeatedJob(
			new Cron\FakeJob(function () {
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
		(new Cron\RepeatedJob(
			new Cron\FakeJob(function () {
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
		(new Cron\RepeatedJob(
			new Cron\FakeJob(function () {
				echo 'OK';
			}, 'FakeJob'),
			'PT10M',
			$this->database
		))->fulfill();
		Assert::same('', ob_get_clean());
	}
}

(new RepeatedJobTest())->run();
