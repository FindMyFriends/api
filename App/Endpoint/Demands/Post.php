<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demands;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Domain\Search;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Storage;
use Klapuch\Uri;
use Klapuch\Validation;
use PhpAmqpLib;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';

	/** @var \Hashids\HashidsInterface */
	private $hashids;

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \PhpAmqpLib\Connection\AbstractConnection */
	private $rabbitMq;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $hashids,
		Application\Request $request,
		Uri\Uri $url,
		Storage\Connection $connection,
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq,
		Access\Seeker $seeker
	) {
		$this->hashids = $hashids;
		$this->request = $request;
		$this->url = $url;
		$this->connection = $connection;
		$this->rabbitMq = $rabbitMq;
		$this->seeker = $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		$url = new Http\CreatedResourceUrl(
			new Uri\RelativeUrl($this->url, 'demands/{id}'),
			[
				'id' => $this->hashids->encode(
					(new Interaction\QueuedDemands(
						new Interaction\IndividualDemands(
							$this->seeker,
							$this->connection
						),
						new Search\AmqpPublisher($this->rabbitMq)
					))->ask(
						(new Validation\ChainedRule(
							new Constraint\StructuredJson(
								new \SplFileInfo(self::SCHEMA)
							),
							new Constraint\DemandRule()
						))->apply(
							(new Internal\DecodedJson(
								$this->request->body()->serialization()
							))->values()
						)
					)
				),
			]
		);
		return new Response\ConcurrentlyCreatedResponse(
			new Response\CreatedResponse(new Response\EmptyResponse(), $url),
			new Http\PostgresETag($this->connection, $url)
		);
	}
}
