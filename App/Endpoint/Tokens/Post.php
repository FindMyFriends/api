<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Tokens;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Output;
use Klapuch\Storage;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $request;
	private $database;
	private $cipher;

	public function __construct(
		Application\Request $request,
		Storage\MetaPDO $database,
		Encryption\Cipher $cipher
	) {
		$this->request = $request;
		$this->database = $database;
		$this->cipher = $cipher;
	}

	public function response(array $parameters): Application\Response {
		try {
			$seeker = (new Access\HarnessedEntrance(
				new Access\TokenEntrance(
					new Access\VerifiedEntrance(
						$this->database,
						new Access\SecureEntrance(
							$this->database,
							$this->cipher
						)
					)
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			))->enter(
				(new Constraint\StructuredJson(
					new \SplFileInfo(self::SCHEMA)
				))->apply(json_decode($this->request->body()->serialization(), true))
			);
			return new Response\JsonResponse(
				new Response\PlainResponse(
					new Output\Json(['token' => $seeker->id()]),
					[],
					HTTP_CREATED
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
