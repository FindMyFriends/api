<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Commands\Schema;

$schemas = new class {
	public function save(array $json, SplFileInfo $file): void {
		file_put_contents(
			$file->getPathname(),
			json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);
	}
};

$demand = new Schema\Demand();
$schemas->save($demand->get(), new SplFileInfo(__DIR__ . '/../App/V1/Demand/schema/get.json'));
$schemas->save($demand->put(), new SplFileInfo(__DIR__ . '/../App/V1/Demand/schema/put.json'));
$schemas->save($demand->post(), new SplFileInfo(__DIR__ . '/../App/V1/Demands/schema/post.json'));

$evolution = new Schema\Evolution();
$schemas->save($evolution->get(), new SplFileInfo(__DIR__ . '/../App/V1/Evolution/schema/get.json'));
$schemas->save($evolution->put(), new SplFileInfo(__DIR__ . '/../App/V1/Evolution/schema/put.json'));
$schemas->save($evolution->post(), new SplFileInfo(__DIR__ . '/../App/V1/Evolutions/schema/post.json'));