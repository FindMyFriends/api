<?php
declare(strict_types = 1);

namespace FindMyFriends\Unit\Http;

use FindMyFriends\Domain\Access;
use FindMyFriends\Http;
use FindMyFriends\TestCase;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

/**
 * @testCase
 */
final class ChosenRoleTest extends TestCase\Runtime {
	public function testAllowedAccessForSingleMatch(): void {
		Assert::true((new Http\ChosenRole(new Access\FakeSeeker(null, ['role' => 'member']), ['member']))->allowed());
	}

	public function testAllowedAccessForMultipleMatches(): void {
		Assert::true((new Http\ChosenRole(new Access\FakeSeeker(null, ['role' => 'member']), ['guest', 'member']))->allowed());
	}

	public function testCaseInsensitiveMatch(): void {
		Assert::true((new Http\ChosenRole(new Access\FakeSeeker(null, ['role' => 'member']), ['MEMBER']))->allowed());
		Assert::true((new Http\ChosenRole(new Access\FakeSeeker(null, ['role' => 'MEMBER']), ['member']))->allowed());
	}

	public function testNoRoleMatchingToGuest(): void {
		Assert::true((new Http\ChosenRole(new Access\FakeSeeker(null, []), ['guest']))->allowed());
		Assert::true((new Http\ChosenRole(new Access\FakeSeeker(null, []), ['GUEST']))->allowed());
	}

	public function testNoMatchForListedRole(): void {
		Assert::false((new Http\ChosenRole(new Access\FakeSeeker(null, ['role' => 'guest']), ['member']))->allowed());
		Assert::false((new Http\ChosenRole(new Access\FakeSeeker(null, ['role' => 'guest']), ['member', 'admin']))->allowed());
	}
}

(new ChosenRoleTest())->run();
