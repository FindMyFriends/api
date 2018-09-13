<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\RefreshTokens;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Output;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	public function __construct(Application\Request $request) {
		$this->request = $request;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		$seeker = (new Access\HarnessedEntrance(
			new Access\RefreshingEntrance(),
			new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
		))->enter(
			(new Constraint\StructuredJson(
				new \SplFileInfo(self::SCHEMA)
			))->apply((new Internal\DecodedJson($this->request->body()->serialization()))->values())
		);
		return new Response\JsonResponse(
			new Response\PlainResponse(
				new Output\Json(['token' => $seeker->id(), 'expiration' => (int) ini_get('session.gc_maxlifetime')]),
				[],
				HTTP_CREATED
			)
		);
	}
}
