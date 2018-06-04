<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Seekers;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Output;
use Klapuch\Storage;
use PhpAmqpLib;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';
	private $request;
	private $database;
	private $rabbitMq;
	private $cipher;

	public function __construct(
		Application\Request $request,
		Storage\MetaPDO $database,
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Encryption\Cipher $cipher
	) {
		$this->request = $request;
		$this->database = $database;
		$this->rabbitMq = $rabbitMq;
		$this->cipher = $cipher;
	}

	public function response(array $parameters): Application\Response {
		try {
			$information = (new Constraint\StructuredJson(
				new \SplFileInfo(self::SCHEMA)
			))->apply(json_decode($this->request->body()->serialization(), true));
			(new Access\HarnessedSeekers(
				new Access\UniqueSeekers(
					$this->database,
					$this->cipher
				),
				new Misc\ApiErrorCallback(HTTP_CONFLICT)
			))->join($information);
			(new Access\AmqpPublisher($this->rabbitMq))->publish($information['email']);
			return new Response\PlainResponse(
				new Output\EmptyFormat(),
				[],
				HTTP_CREATED
			);
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}