<?php
declare(strict_types = 1);

namespace FindMyFriends\Sql;

interface Mapping {
	public function application(array $database): array;
	public function database(array $application): array;
}
