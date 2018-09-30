<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Routing;

use FindMyFriends\Routing;
use FindMyFriends\TestCase;
use Hashids\Hashids;
use Klapuch\Routing\FakeMask;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class SuitedHashIdMaskTest extends TestCase\Runtime {
	public function testReplacingMatchToHashid(): void {
		Assert::same(
			['id' => 1],
			(new Routing\SuitedHashIdMask(
				['id' => 'hashid-demand'],
				new FakeMask(['id' => 'jR']),
				[
					'demand' => new Hashids(),
					'evolution' => new Hashids('abc'),
				]
			))->parameters()
		);
	}

	/**
	 * @throws \UnexpectedValueException Parameter "foo" is not valid
	 */
	public function testThrowingOnInvalidParameters(): void {
		(new Routing\SuitedHashIdMask(
			['id' => 'hashid-demand'],
			new FakeMask(['id' => 'foo']),
			[
				'demand' => new Hashids(),
				'evolution' => new Hashids('abc'),
			]
		))->parameters();
	}

	public function testKeepingForNoMatch(): void {
		Assert::same(
			['id' => 'jR'],
			(new Routing\SuitedHashIdMask(
				['id' => 'hashid-foo'],
				new FakeMask(['id' => 'jR']),
				[
					'demand' => new Hashids(),
					'evolution' => new Hashids('abc'),
				]
			))->parameters()
		);
	}

	public function testKeepingRestUntouched(): void {
		Assert::same(
			['id' => 1, 'evolution_id' => 2],
			(new Routing\SuitedHashIdMask(
				['id' => 'hashid-demand', 'evolution_id' => 'whatever'],
				new FakeMask(['id' => 'jR', 'evolution_id' => 2]),
				[
					'demand' => new Hashids(),
					'evolution' => new Hashids('abc'),
				]
			))->parameters()
		);
	}

	public function testMultipleConversion(): void {
		Assert::same(
			['id' => 1, 'evolution_id' => 2],
			(new Routing\SuitedHashIdMask(
				['id' => 'hashid-demand', 'evolution_id' => 'hashid-evolution'],
				new FakeMask(['id' => 'jR', 'evolution_id' => 'Ay']),
				[
					'demand' => new Hashids(),
					'evolution' => new Hashids('abc'),
				]
			))->parameters()
		);
	}

	public function testPassingOnNothingToConvert(): void {
		Assert::same(
			['page' => 1],
			(new Routing\SuitedHashIdMask(
				[],
				new FakeMask(['page' => 1]),
				[
					'demand' => new Hashids(),
					'evolution' => new Hashids('abc'),
				]
			))->parameters()
		);
	}
}

(new SuitedHashIdMaskTest())->run();
