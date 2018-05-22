<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Domain\Access;

use FindMyFriends\Domain\Access;
use FindMyFriends\TestCase;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

final class CachedSeekerTest extends Tester\TestCase {
	use TestCase\Mockery;

	public function testCallingJustOnce() {
		$seeker = $this->mock(Access\Seeker::class);
		$seeker->shouldReceive('id')
			->once()
			->andReturn('3');
		$seeker->shouldReceive('properties')
			->once()
			->andReturn(['role' => 'master']);
		$cachedSeeker = new Access\CachedSeeker($seeker);
		Assert::same('3', $cachedSeeker->id());
		Assert::same('3', $cachedSeeker->id());
		Assert::same(['role' => 'master'], $cachedSeeker->properties());
		Assert::same(['role' => 'master'], $cachedSeeker->properties());
	}
}

(new CachedSeekerTest())->run();
