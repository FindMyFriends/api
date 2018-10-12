<?php
declare(strict_types = 1);

namespace FindMyFriends\Integration\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

/**
 * @testCase
 */
final class PublicSeekerTest extends TestCase\Runtime {
	use TestCase\TemplateDatabase;

	public function testPropertiesFormat(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@bar.cz']))->try();
		(new Misc\SampleEvolution(
			$this->connection,
			[
				'seeker_id' => $id,
				'general' => [
					'firstname' => 'Dominik',
					'lastname' => 'Klapuch',
					'birth_year' => 1996,
					'ethnic_group_id' => 1,
					'sex' => 'man',
				],
			]
		))->try();
		(new Misc\SamplePostgresData(
			$this->connection,
			'seeker_contact',
			['seeker_id' => $id, 'facebook' => 'test_fb', 'instagram' => 'test_ig', 'phone_number' => null]
		))->try();
		['id' => $met] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		['id' => $demand] = (new Misc\SampleDemand($this->connection, ['seeker_id' => $id]))->try();
		['id' => $evolution] = (new Misc\SampleEvolution($this->connection, ['seeker_id' => $met]))->try();
		(new Misc\SamplePostgresData($this->connection, 'soulmate', ['demand_id' => $demand, 'evolution_id' => $evolution, 'is_exposed' => true]))->try();
		$seeker = new Access\PublicSeeker(new Access\FakeSeeker((string) $id), new Access\FakeSeeker((string) $met), $this->connection);
		Assert::same((string) $met, $seeker->id());
		Assert::same(
			[
				'general' => [
					'firstname' => 'Dominik',
					'lastname' => 'Klapuch',
					'birth_year' => 1996,
					'ethnic_group_id' => 1,
					'sex' => 'man',
				],
				'contact' => [
					'facebook' => 'test_fb',
					'instagram' => 'test_ig',
					'phone_number' => null,
				],
			],
			$seeker->properties()
		);
	}

	public function testThrowingOnNotMet(): void {
		['id' => $id] = (new Misc\SamplePostgresData($this->connection, 'seeker', ['email' => 'foo@bar.cz']))->try();
		['id' => $foreigner] = (new Misc\SamplePostgresData($this->connection, 'seeker'))->try();
		(new Misc\SampleEvolution(
			$this->connection,
			[
				'seeker_id' => $id,
				'general' => [
					'firstname' => 'Dominik',
					'lastname' => 'Klapuch',
					'birth_year' => 1996,
					'ethnic_group_id' => 1,
					'sex' => 'man',
				],
			]
		))->try();
		(new Misc\SamplePostgresData(
			$this->connection,
			'seeker_contact',
			['seeker_id' => $id, 'facebook' => 'test_fb', 'instagram' => 'test_ig', 'phone_number' => null]
		))->try();
		$seeker = new Access\PublicSeeker(new Access\FakeSeeker((string) $id), new Access\FakeSeeker((string) $foreigner), $this->connection);
		Assert::same((string) $foreigner, $seeker->id());
		Assert::exception(static function () use ($seeker) {
			$seeker->properties();
		}, \UnexpectedValueException::class, sprintf('Seeker %d is unknown', $foreigner));
	}
}

(new PublicSeekerTest())->run();
