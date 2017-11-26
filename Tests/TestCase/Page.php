<?php
declare(strict_types = 1);
namespace FindMyFriends\TestCase;

trait Page {
	use TemplateDatabase {
		TemplateDatabase::setUp as databaseSetUp;
	}

	protected function setUp(): void {
		parent::setUp();
		$this->databaseSetUp();
	}
}