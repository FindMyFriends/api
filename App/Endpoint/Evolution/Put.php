<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution;

use Elasticsearch;
use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Request;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Storage;
use Klapuch\Uri;
use Klapuch\Validation;

final class Put implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/put.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Elasticsearch\Client */
	private $elasticsearch;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		Storage\Connection $connection,
		Elasticsearch\Client $elasticsearch,
		Access\Seeker $seeker
	) {
		$this->request = $request;
		$this->url = $url;
		$this->connection = $connection;
		$this->elasticsearch = $elasticsearch;
		$this->seeker = $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function response(array $parameters): Application\Response {
		(new Evolution\SyncChange(
			$parameters['id'],
			new Evolution\ChainedChange(
				new Evolution\HarnessedChange(
					new Evolution\ExistingChange(
						new Evolution\FakeChange(),
						$parameters['id'],
						$this->connection
					),
					new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
				),
				new Evolution\HarnessedChange(
					new Evolution\VisibleChange(
						new Evolution\FakeChange(),
						$parameters['id'],
						$this->seeker,
						$this->connection
					),
					new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
				),
				new Evolution\StoredChange($parameters['id'], $this->connection)
			),
			$this->elasticsearch
		))->affect(
			(new Validation\ChainedRule(
				new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
				new Constraint\EvolutionRule()
			))->apply(
				(new Internal\DecodedJson(
					(new Request\FriendlyRequest(
						new Request\ConcurrentlyControlledRequest(
							$this->request,
							new Http\PostgresETag($this->connection, $this->url)
						),
						'You already affected evolution change with newer data.'
					))->body()->serialization()
				))->values()
			)
		);
		return new Response\EmptyResponse();
	}
}
