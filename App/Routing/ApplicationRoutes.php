<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use Elasticsearch;
use FindMyFriends\Domain;
use FindMyFriends\Domain\Access;
use FindMyFriends\Endpoint;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\View;
use Klapuch\Application;
use Klapuch\Encryption;
use Klapuch\Routing;
use Klapuch\Storage;
use Klapuch\Uri;
use PhpAmqpLib;
use Predis;

/**
 * Routes for whole application
 */
final class ApplicationRoutes implements Routing\Routes {
	/** @var \Klapuch\Uri\Uri */
	private $uri;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \Predis\ClientInterface */
	private $redis;

	/** @var \Elasticsearch\Client */
	private $elasticsearch;

	/** @var \PhpAmqpLib\Connection\AMQPLazyConnection */
	private $rabbitMq;

	/** @var \Klapuch\Encryption\Cipher */
	private $cipher;

	/** @var mixed[] */
	private $hashids;

	public function __construct(
		Uri\Uri $uri,
		Storage\MetaPDO $database,
		Predis\ClientInterface $redis,
		Elasticsearch\Client $elasticsearch,
		PhpAmqpLib\Connection\AMQPLazyConnection $rabbitMq,
		Encryption\Cipher $cipher,
		array $hashids
	) {
		$this->uri = $uri;
		$this->database = $database;
		$this->redis = $redis;
		$this->elasticsearch = $elasticsearch;
		$this->rabbitMq = $rabbitMq;
		$this->cipher = $cipher;
		$this->hashids = $hashids;
	}

	public function matches(): array {
		$seeker = (new Domain\Access\HarnessedEntrance(
			new Domain\Access\RateLimitedEntrance(
				new Access\ApiEntrance($this->database),
				$this->redis
			),
			new Misc\ApiErrorCallback(HTTP_TOO_MANY_REQUESTS)
		))->enter((new Application\PlainRequest())->headers());
		return [
			'demands [OPTIONS]' => new Endpoint\Preflight(
				new Endpoint\Demands\Options($this->database, $this->redis),
				new Application\PlainRequest()
			),
			'demands [GET]' => new View\AuthenticatedView(
				new Endpoint\Demands\Get(
					$this->hashids['demand'],
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands/{id} [GET]' => new View\AuthenticatedView(
				new Endpoint\Demand\Get(
					$this->hashids['demand'],
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands/{demand_id}/soulmate_requests [GET]' => new View\AuthenticatedView(
				new Endpoint\Demand\SoulmateRequests\Get(
					$this->uri,
					$this->database
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands/{demand_id}/soulmate_requests [POST]' => new View\AuthenticatedView(
				new Endpoint\Demand\SoulmateRequests\Post(
					$this->uri,
					$this->database,
					$this->rabbitMq
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands [POST]' => new View\AuthenticatedView(
				new Endpoint\Demands\Post(
					$this->hashids['demand'],
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$this->rabbitMq,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands/{id} [PUT]' => new View\AuthenticatedView(
				new Endpoint\Demand\Put(
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands/{id} [PATCH]' => new View\AuthenticatedView(
				new Endpoint\Demand\Patch(
					new Application\PlainRequest(),
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands/{id} [DELETE]' => new View\AuthenticatedView(
				new Endpoint\Demand\Delete(
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'evolutions [OPTIONS]' => new Endpoint\Preflight(
				new View\AuthenticatedView(
					new Endpoint\Evolutions\Options(
						$this->database,
						$this->redis,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				),
				new Application\PlainRequest()
			),
			'evolutions [POST]' => new View\AuthenticatedView(
				new Endpoint\Evolutions\Post(
					$this->hashids['evolution'],
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$this->elasticsearch,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'evolutions [GET]' => new View\AuthenticatedView(
				new Endpoint\Evolutions\Get(
					$this->hashids['evolution'],
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'evolutions/{id} [GET]' => new View\AuthenticatedView(
				new Endpoint\Evolution\Get(
					$this->hashids['evolution'],
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'evolutions/{id}/locations [GET]' => new View\AuthenticatedView(
				new Endpoint\Evolution\Locations\Get(
					$this->hashids['location'],
					$this->hashids['evolution'],
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'evolutions/{id}/locations [POST]' => new View\AuthenticatedView(
				new Endpoint\Evolution\Locations\Post(
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'evolutions/{evolution_id}/locations/{id} [DELETE]' => new View\AuthenticatedView(
				new Endpoint\Evolution\Locations\Delete(
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'evolutions/{id} [DELETE]' => new View\AuthenticatedView(
				new Endpoint\Evolution\Delete(
					$this->database,
					$this->elasticsearch,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'evolutions/{id} [PUT]' => new View\AuthenticatedView(
				new Endpoint\Evolution\Put(
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$this->elasticsearch,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'soulmates/{id} [PATCH]' => new View\AuthenticatedView(
				new Endpoint\Soulmate\Patch(
					new Application\PlainRequest(),
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands/{demand_id}/soulmates [GET]' => new View\AuthenticatedView(
				new Endpoint\Demand\Soulmates\Get(
					$this->hashids,
					$this->uri,
					$this->database,
					$this->elasticsearch
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'demands/{demand_id}/soulmates [HEAD]' => new View\AuthenticatedView(
				new Endpoint\Demand\Soulmates\Head(
					$this->uri,
					$this->database,
					$this->elasticsearch
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'seekers [POST]' => new Endpoint\Seekers\Post(
				new Application\PlainRequest(),
				$this->database,
				$this->rabbitMq,
				$this->cipher
			),
			'tokens [POST]' => new Endpoint\Tokens\Post(
				new Application\PlainRequest(),
				$this->database,
				$this->cipher
			),
			'tokens [DELETE]' => new View\AuthenticatedView(
				new Endpoint\Tokens\Delete(),
				new Http\ChosenRole($seeker, ['member'])
			),
		];
	}
}
