<?php
declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

use FindMyFriends\Schema;
use Klapuch\Configuration;
use Klapuch\Storage;

$schemas = new class {
	public function save(array $json, SplFileInfo $file): void {
		@mkdir($file->getPath(), 0777, true); // @ directory may exists
		file_put_contents(
			$file->getPathname(),
			json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);
	}
};

$configuration = (new Configuration\ValidIni(
	new SplFileInfo(__DIR__ . '/../App/Configuration/.secrets.ini')
))->read();
$database = new Storage\SafePDO(
	$configuration['DATABASE']['dsn'],
	$configuration['DATABASE']['user'],
	$configuration['DATABASE']['password']
);

$demand = new Schema\Demand\Structure($database);
$schemas->save($demand->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Demand/schema/get.json'));
$schemas->save($demand->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Demands/schema/get.json'));
$schemas->save($demand->put(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Demand/schema/put.json'));
$schemas->save($demand->patch(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Demand/schema/patch.json'));
$schemas->save($demand->post(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Demands/schema/post.json'));

$evolution = new Schema\Evolution\Structure($database);
$schemas->save($evolution->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Evolution/schema/get.json'));
$schemas->save($evolution->put(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Evolution/schema/put.json'));
$schemas->save($evolution->post(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Evolutions/schema/post.json'));

$description = new Schema\Description\Structure($database);
$schemas->save($description->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Description/schema/get.json'));
$schemas->save($description->put(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Description/schema/put.json'));
$schemas->save($description->post(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Descriptions/schema/post.json'));

$soulmate = new Schema\Soulmate\Structure();
$schemas->save($soulmate->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Demand/Soulmates/schema/get.json'));
$schemas->save($soulmate->patch(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Soulmate/schema/patch.json'));

$soulmateRequest = new FindMyFriends\Schema\SoulmateRequest\Structure($database);
$schemas->save($soulmateRequest->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Demand/SoulmateRequests/schema/get.json'));

$seeker = new FindMyFriends\Schema\Seeker\Structure($database);
$schemas->save($seeker->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Seeker/schema/get.json'));
$schemas->save($seeker->post(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Seekers/schema/post.json'));

$token = new FindMyFriends\Schema\Token\Structure();
$schemas->save($token->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Token/schema/get.json'));
$schemas->save($token->post(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Tokens/schema/post.json'));

$location = new FindMyFriends\Schema\Location\Structure($database);
$schemas->save($location->get(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Evolution/Locations/schema/get.json'));
$schemas->save($location->post(), new SplFileInfo(__DIR__ . '/../App/Endpoint/Evolution/Locations/schema/post.json'));
