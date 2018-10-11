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
final class PubliclyPrivateSeekerTest extends TestCase\Runtime {
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
		$seeker = new Access\PubliclyPrivateSeeker(new Access\FakeSeeker((string) $id), $this->connection);
		Assert::same((string) $id, $seeker->id());
		Assert::same(
			[
				'email' => 'foo@bar.cz',
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
}

(new PubliclyPrivateSeekerTest())->run();
