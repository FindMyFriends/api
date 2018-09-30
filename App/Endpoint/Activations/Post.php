<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Activations;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Output;
use Klapuch\Storage;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(
		Application\Request $request,
		Storage\Connection $connection
	) {
		$this->request = $request;
		$this->connection = $connection;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		$verification = (new Constraint\StructuredJson(
			new \SplFileInfo(self::SCHEMA)
		))->apply((new Internal\DecodedJson($this->request->body()->serialization()))->values());
		(new Access\ChainedVerificationCode(
			new Access\HarnessedVerificationCode(
				new Access\ExistingVerificationCode(
					new Access\FakeVerificationCode(),
					$verification['code'],
					$this->connection
				),
				new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
			),
			new Access\HarnessedVerificationCode(
				new Access\ThrowawayVerificationCode(
					$verification['code'],
					$this->connection
				),
				new Misc\ApiErrorCallback(HTTP_GONE)
			)
		))->use();
		return new Response\PlainResponse(
			new Output\EmptyFormat(),
			[],
			HTTP_CREATED
		);
	}
}
