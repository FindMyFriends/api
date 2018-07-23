<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Integration\Scheduling;

use FindMyFriends\Scheduling;
use FindMyFriends\TestCase;
use Klapuch\Storage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class MarkedJobTest extends Tester\TestCase {
	use TestCase\TemplateDatabase;

	public function testSubsequentId() {
		(new Scheduling\MarkedJob(new Scheduling\FakeJob(null, 'FakeJob'), $this->database))->fulfill();
		$rows = (new Storage\TypedQuery(
			$this->database,
			'SELECT *
			FROM log.cron_jobs
			ORDER BY marked_at ASC'
		))->rows();
		Assert::count(2, $rows);
		Assert::same('FakeJob', $rows[0]['name']);
		Assert::same('FakeJob', $rows[1]['name']);
		Assert::same('processing', $rows[0]['status']);
		Assert::same('succeed', $rows[1]['status']);
		Assert::null($rows[0]['self_id']);
		Assert::same($rows[0]['id'], $rows[1]['self_id']);
	}

	public function testMarkingExceptionAsFailed() {
		Assert::exception(
			function() {
				(new Scheduling\MarkedJob(
					new Scheduling\FakeJob(function () {
						throw new \DomainException('Oops');
					}, 'FakeJob'),
					$this->database
				))->fulfill();
			},
			\DomainException::class,
			'Oops'
		);
		$rows = (new Storage\TypedQuery(
			$this->database,
			'SELECT *
			FROM log.cron_jobs
			ORDER BY marked_at ASC'
		))->rows();
		Assert::count(2, $rows);
		Assert::same('processing', $rows[0]['status']);
		Assert::same('failed', $rows[1]['status']);
		Assert::null($rows[0]['self_id']);
		Assert::same($rows[0]['id'], $rows[1]['self_id']);
	}
}

(new MarkedJobTest())->run();