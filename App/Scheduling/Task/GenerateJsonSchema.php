<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use FindMyFriends\Scheduling;
use FindMyFriends\Schema;
use Klapuch\Http;
use Klapuch\Internal;
use Klapuch\Uri;

final class GenerateJsonSchema implements Scheduling\Job {
	/** @var \PDO */
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function fulfill(): void {
		$schemas = new class {
			/**
			 * @param string $schema
			 * @throws \UnexpectedValueException
			 */
			public function validate(string $schema): void {
				$response = (new Http\BasicRequest(
					'POST',
					new Uri\ValidUrl('https://www.jsonschemavalidator.net/api/jsonschema/validate'),
					[
						CURLOPT_HTTPHEADER => [
							'Content-Type: application/json',
							'X-Csrf-Token: LsAV3irUxESTZz-djmy6u5czf122eyTgu3yvdi6MSOwQANDhsOHOQzBZrqPku09Z8KS8BIE406uNXXeAaSycv978wm81:EYgPsfAI3loDTk9UhNmva8lcEE5KwhHSUbD_zTktXHmaO7iA36crJ8eAB0rum1vjF3VeIaKiC4GIPRTtJG8ydDuUdt41',
						],
					],
					(new Internal\EncodedJson(['json' => '', 'schema' => $schema]))->value()
				))->send();
				$validation = (new Internal\DecodedJson($response->body()))->values();
				if ($validation['valid'] === false) {
					throw new \Exception('JSON schema is not valid');
				}
			}

			public function save(array $json, \SplFileInfo $file): void {
				@mkdir($file->getPath(), 0777, true); // @ directory may exists
				$schema = (new Internal\EncodedJson($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))->value();
				try {
					$this->validate($schema);
				} catch (\UnexpectedValueException $e) {
					throw new \Exception(sprintf('JSON schema %s is not valid', $file->getPathname()), 0, $e);
				}
				file_put_contents($file->getPathname(), $schema);
			}
		};
		$demand = new Schema\Demand\Structure($this->database);
		$schemas->save($demand->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/schema/get.json'));
		$schemas->save($demand->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demands/schema/get.json'));
		$schemas->save($demand->put(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/schema/put.json'));
		$schemas->save($demand->patch(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/schema/patch.json'));
		$schemas->save($demand->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demands/schema/post.json'));

		$evolution = new Schema\Evolution\Structure($this->database);
		$schemas->save($evolution->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolution/schema/get.json'));
		$schemas->save($evolution->put(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolution/schema/put.json'));
		$schemas->save($evolution->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolutions/schema/post.json'));

		$description = new Schema\Description\Structure($this->database);
		$schemas->save($description->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Description/schema/get.json'));
		$schemas->save($description->put(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Description/schema/put.json'));
		$schemas->save($description->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Descriptions/schema/post.json'));

		$soulmate = new Schema\Soulmate\Structure();
		$schemas->save($soulmate->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/Soulmates/schema/get.json'));
		$schemas->save($soulmate->patch(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Soulmate/schema/patch.json'));

		$soulmateRequest = new Schema\SoulmateRequest\Structure($this->database);
		$schemas->save($soulmateRequest->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/SoulmateRequests/schema/get.json'));

		$seeker = new Schema\Seeker\Structure($this->database);
		$schemas->save($seeker->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Seeker/schema/get.json'));
		$schemas->save($seeker->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Seekers/schema/post.json'));

		$token = new Schema\Token\Structure();
		$schemas->save($token->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Token/schema/get.json'));
		$schemas->save($token->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Tokens/schema/post.json'));

		$evolutionLocation = new Schema\Evolution\Location\Structure($this->database);
		$schemas->save($evolutionLocation->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolution/Locations/schema/get.json'));
		$schemas->save($evolutionLocation->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolution/Locations/schema/post.json'));

		$demandLocation = new Schema\Demand\Location\Structure($this->database);
		$schemas->save($demandLocation->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/Locations/schema/get.json'));
		$schemas->save($demandLocation->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/Locations/schema/post.json'));
	}

	public function name(): string {
		return 'GenerateJsonSchema';
	}
}
