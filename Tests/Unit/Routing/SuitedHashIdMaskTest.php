<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Routing;

use FindMyFriends\Routing;
use Hashids\Hashids;
use Klapuch\Routing\FakeMask;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class SuitedHashIdMaskTest extends Tester\TestCase {
	public function testMaskWithHashIdOnMatchingPattern() {
		Assert::same(
			['id' => 1],
			(new Routing\SuitedHashIdMask(
				new FakeMask(['id' => 'jR']),
				[
					'demand' => [
						'hashid' => new Hashids(),
						'paths' => ['^v1/demand.+$'],
						'parameters' => ['id' => 'demand'],
					],
					'evolution' => [
						'hashid' => new Hashids('abc'),
						'paths' => ['^v1/evolution.+$'],
						'parameters' => ['id' => 'evolution'],
					],
				],
				'v1/demands/jR'
			))->parameters()
		);
	}

	public function testNoMatchWithReturnedOrigin() {
		Assert::same(
			['id' => 'jR'],
			(new Routing\SuitedHashIdMask(
				new FakeMask(['id' => 'jR']),
				[
					'demand' => [
						'hashid' => new Hashids(),
						'paths' => ['^v1/demand.+$'],
						'parameters' => ['id' => 'demand'],
					],
					'evolution' => [
						'hashid' => new Hashids('abc'),
						'paths' => ['^v1/evolution.+$'],
						'parameters' => ['id' => 'evolution'],
					],
				],
				'v1/ou'
			))->parameters()
		);
	}

	public function testUsingReferenceToHashid() {
		Assert::equal(
			['id' => 1, 'demand_id' => 2],
			(new Routing\SuitedHashIdMask(
				new FakeMask(['id' => 'jR', 'demand_id' => 'Ay']),
				[
					'demand' => [
						'hashid' => new Hashids('abc'),
						'paths' => ['^v1/demand.+$'],
						'parameters' => ['id' => 'demand'],
					],
					'evolution' => [
						'hashid' => new Hashids(),
						'paths' => ['^v1/evolution.+$'],
						'parameters' => ['id' => 'evolution', 'demand_id' => 'demand'],
					],
				],
				'v1/evolution/jR'
			))->parameters()
		);
	}

	public function testKeepingParameters() {
		Assert::same(
			['id' => 1, 'name' => ''],
			(new Routing\SuitedHashIdMask(
				new FakeMask(['id' => 'jR', 'name' => '']),
				[
					'demand' => [
						'hashid' => new Hashids(),
						'paths' => ['^v1/demand.+$'],
						'parameters' => ['id' => 'demand'],
					],
				],
				'v1/demands/jR'
			))->parameters()
		);
	}
}

(new SuitedHashIdMaskTest())->run();
