<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Seekers;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Storage;
use Klapuch\Uri;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $request;
	private $url;
	private $database;
	private $cipher;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Encryption\Cipher $cipher
	) {
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function response(array $parameters): Application\Response {
		try {
			(new Access\HarnessedSeekers(
				new Access\UniqueSeekers(
					$this->database,
					$this->cipher
				),
				new Misc\ApiErrorCallback(HTTP_CONFLICT)
			))->join(
				(new Constraint\StructuredJson(
					new \SplFileInfo(self::SCHEMA)
				))->apply(json_decode($this->request->body()->serialization(), true))
			);
			return new Response\CreatedResponse(new Response\EmptyResponse(), $this->url);
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
