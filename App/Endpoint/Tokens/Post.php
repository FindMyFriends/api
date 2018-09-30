<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Tokens;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Internal;
use Klapuch\Output;
use Klapuch\Storage;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Klapuch\Encryption\Cipher */
	private $cipher;

	public function __construct(
		Application\Request $request,
		Storage\Connection $connection,
		Encryption\Cipher $cipher
	) {
		$this->request = $request;
		$this->connection = $connection;
		$this->cipher = $cipher;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		$seeker = (new Access\HarnessedEntrance(
			new Access\TokenEntrance(
				new Access\VerifiedEntrance(
					$this->connection,
					new Access\SecureEntrance(
						$this->connection,
						$this->cipher
					)
				)
			),
			new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
		))->enter(
			(new Constraint\StructuredJson(
				new \SplFileInfo(self::SCHEMA)
			))->apply((new Internal\DecodedJson($this->request->body()->serialization()))->values())
		);
		return new Response\JsonResponse(
			new Response\PlainResponse(
				new Output\Json(['token' => $seeker->id(), 'expiration' => $seeker->properties()['expiration']]),
				[],
				HTTP_CREATED
			)
		);
	}
}
