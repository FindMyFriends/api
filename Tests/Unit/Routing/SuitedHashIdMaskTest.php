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
					],
					'evolution' => [
						'hashid' => new Hashids('abc'),
						'paths' => ['^v1/evolution.+$'],
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
					],
					'evolution' => [
						'hashid' => new Hashids('abc'),
						'paths' => ['^v1/evolution.+$'],
					],
				],
				'v1/ou'
			))->parameters()
		);
	}
}

(new SuitedHashIdMaskTest())->run();