<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Http;

use FindMyFriends\Http;
use Klapuch\Access;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class ChosenRoleTest extends Tester\TestCase {
	public function testAllowedAccessForSingleMatch() {
		Assert::true((new Http\ChosenRole(new Access\FakeUser(null, ['role' => 'member']), ['member']))->allowed());
	}

	public function testAllowedAccessForMultipleMatches() {
		Assert::true((new Http\ChosenRole(new Access\FakeUser(null, ['role' => 'member']), ['guest', 'member']))->allowed());
	}

	public function testCaseInsensitiveMatch() {
		Assert::true((new Http\ChosenRole(new Access\FakeUser(null, ['role' => 'member']), ['MEMBER']))->allowed());
		Assert::true((new Http\ChosenRole(new Access\FakeUser(null, ['role' => 'MEMBER']), ['member']))->allowed());
	}

	public function testNoRoleMatchingToGuest() {
		Assert::true((new Http\ChosenRole(new Access\FakeUser(null, []), ['guest']))->allowed());
		Assert::true((new Http\ChosenRole(new Access\FakeUser(null, []), ['GUEST']))->allowed());
	}

	public function testNoMatchForListedRole() {
		Assert::false((new Http\ChosenRole(new Access\FakeUser(null, ['role' => 'guest']), ['member']))->allowed());
		Assert::false((new Http\ChosenRole(new Access\FakeUser(null, ['role' => 'guest']), ['member', 'admin']))->allowed());
	}
}

(new ChosenRoleTest())->run();