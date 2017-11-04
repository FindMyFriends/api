<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.2
 */
namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class IntRange extends Tester\TestCase {
	public function testPassingOnIntRange() {
		Assert::true((new Constraint\IntRange())->satisfied('(10,20)'));
		Assert::noError(function() {
			(new Constraint\IntRange())->apply('(10,20)');
		});
	}

	public function testPassingWithZeros() {
		Assert::true((new Constraint\IntRange())->satisfied('(0,20)'));
	}

	public function testPassingAsNotFull() {
		Assert::true((new Constraint\IntRange())->satisfied('(0,)'));
		Assert::true((new Constraint\IntRange())->satisfied('(,0)'));
	}

	public function testFailingOnFloat() {
		Assert::false((new Constraint\IntRange())->satisfied('(10.0,20)'));
		Assert::false((new Constraint\IntRange())->satisfied('(10,20.0)'));
		Assert::false((new Constraint\IntRange())->satisfied('(20,INF)'));
		Assert::exception(function() {
			(new Constraint\IntRange())->apply('(20,INF)');
		}, \UnexpectedValueException::class, 'Range must be numeric');
	}

	public function testNoModificationOnApplication() {
			Assert::same('(10,20)', (new Constraint\IntRange())->apply('(10,20)'));
	}
}

(new IntRange())->run();