<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use FindMyFriends\Scheduling;
use FindMyFriends\Schema;
use Klapuch\Http;
use Klapuch\Internal;
use Klapuch\Storage;
use Klapuch\Uri;

final class GenerateJsonSchema implements Scheduling\Job {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection) {
		$this->connection = $connection;
	}

	public function fulfill(): void {
		$schemas = new class {
			/**
			 * @param string $schema
			 * @throws \UnexpectedValueException
			 */
			private function validate(string $schema): void {
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

		$this->withoutRemains();

		$demand = new Schema\Demand\Structure($this->connection);
		$schemas->save($demand->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/schema/get.json'));
		$schemas->save($demand->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demands/schema/get.json'));
		$schemas->save($demand->put(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/schema/put.json'));
		$schemas->save($demand->patch(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/schema/patch.json'));
		$schemas->save($demand->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demands/schema/post.json'));

		$evolution = new Schema\Evolution\Structure($this->connection);
		$schemas->save($evolution->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolution/schema/get.json'));
		$schemas->save($evolution->put(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolution/schema/put.json'));
		$schemas->save($evolution->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolutions/schema/post.json'));

		$description = new Schema\Description\Structure($this->connection);
		$schemas->save($description->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Description/schema/get.json'));
		$schemas->save($description->put(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Description/schema/put.json'));
		$schemas->save($description->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Descriptions/schema/post.json'));

		$soulmate = new Schema\Soulmate\Structure($this->connection);
		$schemas->save($soulmate->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Soulmates/schema/get.json'));
		$schemas->save($soulmate->patch(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Soulmate/schema/patch.json'));

		$soulmateRequest = new Schema\SoulmateRequest\Structure($this->connection);
		$schemas->save($soulmateRequest->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/SoulmateRequests/schema/get.json'));

		$seeker = new Schema\Seeker\Structure($this->connection);
		$schemas->save($seeker->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Seeker/schema/get.json'));
		$schemas->save($seeker->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Seekers/schema/post.json'));

		$me = new Schema\Seeker\Me\Structure($this->connection);
		$schemas->save($me->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Seekers/Me/schema/get.json'));

		$token = new Schema\Token\Structure();
		$schemas->save($token->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Tokens/schema/post.json'));

		$refreshToken = new Schema\RefreshToken\Structure();
		$schemas->save($refreshToken->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/RefreshTokens/schema/post.json'));

		$evolutionSpot = new Schema\Evolution\Spot\Structure($this->connection);
		$schemas->save($evolutionSpot->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolution/Spots/schema/get.json'));
		$schemas->save($evolutionSpot->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Evolution/Spots/schema/post.json'));

		$demandSpot = new Schema\Demand\Spot\Structure($this->connection);
		$schemas->save($demandSpot->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/Spots/schema/get.json'));
		$schemas->save($demandSpot->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Demand/Spots/schema/post.json'));

		$spot = new Schema\Spot\Structure($this->connection);
		$schemas->save($spot->put(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Spot/schema/put.json'));
		$schemas->save($spot->patch(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Spot/schema/patch.json'));

		$activation = new Schema\Activation\Structure();
		$schemas->save($activation->post(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Activations/schema/post.json'));

		$notification = new Schema\Notification\Structure($this->connection);
		$schemas->save($notification->get(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Notifications/schema/get.json'));
		$schemas->save($notification->patch(), new \SplFileInfo(__DIR__ . '/../../Endpoint/Notification/schema/patch.json'));
	}

	public function name(): string {
		return 'GenerateJsonSchema';
	}

	private function withoutRemains(): void {
		foreach (new \CallbackFilterIterator(
			new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator(__DIR__ . '/../../Endpoint', \RecursiveDirectoryIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::SELF_FIRST,
				\RecursiveIteratorIterator::CATCH_GET_CHILD
			),
			static function (\SplFileInfo $file): bool {
				return $file->isDir() && (
					file_exists(sprintf('%s/get.json', $file->getPathname()))
					|| file_exists(sprintf('%s/post.json', $file->getPathname()))
					|| file_exists(sprintf('%s/put.json', $file->getPathname()))
					|| file_exists(sprintf('%s/patch.json', $file->getPathname()))
				);
			}
		) as $directory) {
			/** @var \SplFileInfo $directory */
			array_map('unlink', glob(sprintf('%s/*.json', $directory->getPathname())));
			if (!rmdir($directory->getPathName())) {
				throw new \RuntimeException(sprintf('%s was not removed', $directory->getPathname()));
			}
		}
	}
}
