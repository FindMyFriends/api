<?php
declare(strict_types = 1);

/**
 * @testCase
 * @phpVersion > 7.2
 */

namespace FindMyFriends\Unit\Constraint;

use FindMyFriends\Constraint;
use Klapuch\Validation;
use Tester;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.php';

final class IfNotNullRuleTest extends Tester\TestCase {
	public function testIgnoredRuleForNullSubject() {
		$rule = new Validation\FakeRule(false, new \DomainException('foo'));
		Assert::true((new Constraint\IfNotNullRule($rule))->satisfied(null));
		Assert::null((new Constraint\IfNotNullRule($rule))->apply(null));
	}

	public function testUsingRuleForNotNull() {
		$rule = new Validation\FakeRule(false, new \DomainException('foo'));
		Assert::false((new Constraint\IfNotNullRule($rule))->satisfied('X'));
		Assert::exception(function() use ($rule) {
			(new Constraint\IfNotNullRule($rule))->apply('X');
		}, \DomainException::class, 'foo');
	}
}

(new IfNotNullRuleTest())->run();
