<?php
declare(strict_types = 1);

namespace FindMyFriends\TestCase;

use Tester;

abstract class Runtime extends Tester\TestCase {
	public function run(): void {
		if (getenv('PHPSTAN') === '1') {
			Tester\Environment::$checkAssertions = false;
		} else {
			parent::run();
		}
	}
}
