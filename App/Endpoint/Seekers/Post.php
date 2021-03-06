<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Seekers;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Internal;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Validation;
use PhpAmqpLib;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \PhpAmqpLib\Connection\AbstractConnection */
	private $rabbitMq;

	/** @var \Klapuch\Encryption\Cipher */
	private $cipher;

	public function __construct(
		Application\Request $request,
		Storage\Connection $connection,
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Encryption\Cipher $cipher
	) {
		$this->request = $request;
		$this->connection = $connection;
		$this->rabbitMq = $rabbitMq;
		$this->cipher = $cipher;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		$information = (new Validation\ChainedRule(
			new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
			new Constraint\SeekerRule()
		))->apply((new Internal\DecodedJson($this->request->body()->serialization()))->values());
		(new Access\HarnessedSeekers(
			new Access\UniqueSeekers(
				$this->connection,
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
	}
}
