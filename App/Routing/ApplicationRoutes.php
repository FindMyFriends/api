<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use FindMyFriends\Domain\Access;
use FindMyFriends\Elasticsearch\LazyElasticsearch;
use FindMyFriends\Endpoint;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Request\CachedRequest;
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

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Predis\ClientInterface */
	private $redis;

	/** @var \FindMyFriends\Elasticsearch\LazyElasticsearch */
	private $elasticsearch;

	/** @var \PhpAmqpLib\Connection\AMQPLazyConnection */
	private $rabbitMq;

	/** @var \Klapuch\Encryption\Cipher */
	private $cipher;

	/** @var mixed[] */
	private $hashids;

	public function __construct(
		Uri\Uri $uri,
		Storage\Connection $connection,
		Predis\ClientInterface $redis,
		LazyElasticsearch $elasticsearch,
		PhpAmqpLib\Connection\AMQPLazyConnection $rabbitMq,
		Encryption\Cipher $cipher,
		array $hashids
	) {
		$this->uri = $uri;
		$this->connection = $connection;
		$this->redis = $redis;
		$this->elasticsearch = $elasticsearch;
		$this->rabbitMq = $rabbitMq;
		$this->cipher = $cipher;
		$this->hashids = $hashids;
	}

	public function matches(): array {
		$request = new CachedRequest(new Application\PlainRequest());
		$seeker = (new Access\HarnessedEntrance(
			new Access\RateLimitedEntrance(
				new Access\PgEntrance(
					new Access\ApiEntrance($this->connection),
					$this->connection
				),
				$this->redis
			),
			new Misc\ApiErrorCallback(HTTP_TOO_MANY_REQUESTS)
		))->enter($request->headers());
		return [
			'activations [POST]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Activations\Post(
						$request,
						$this->connection
					),
					new Http\ChosenRole($seeker, ['guest'])
				);
			},
			'descriptions [OPTIONS]' => function() use ($request): Application\View {
				return new Endpoint\Preflight(
					new Endpoint\Descriptions\Options($this->connection, $this->redis),
					$request
				);
			},
			'demands [OPTIONS]' => function() use ($request): Application\View {
				return new Endpoint\Preflight(
					new Endpoint\Demands\Options($this->connection, $this->redis),
					$request
				);
			},
			'demands [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demands\Get(
						$this->hashids['demand'],
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id} [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Get(
						$this->hashids['demand'],
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{demand_id}/soulmate_requests [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\SoulmateRequests\Get(
						$this->uri,
						$this->connection
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{demand_id}/soulmate_requests [POST]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\SoulmateRequests\Post(
						$this->uri,
						$this->connection,
						$this->rabbitMq
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands [POST]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demands\Post(
						$this->hashids['demand'],
						$request,
						$this->uri,
						$this->connection,
						$this->rabbitMq,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id} [PUT]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Put(
						$request,
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id} [PATCH]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Patch(
						$request,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id} [DELETE]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Delete(
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id}/spots [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Spots\Get(
						$this->hashids['spot'],
						$this->hashids['demand'],
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id}/spots [POST]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Spots\Post(
						$request,
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{demand_id}/spots/{id} [DELETE]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Spots\Delete(
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions [OPTIONS]' => function() use ($seeker, $request): Application\View {
				return new Endpoint\Preflight(
					new View\AuthenticatedView(
						new Endpoint\Evolutions\Options(
							$this->connection,
							$this->redis,
							$seeker
						),
						new Http\ChosenRole($seeker, ['member'])
					),
					$request
				);
			},
			'evolutions [POST]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolutions\Post(
						$this->hashids['evolution'],
						$request,
						$this->uri,
						$this->connection,
						$this->elasticsearch->create(),
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolutions\Get(
						$this->hashids['evolution'],
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{id} [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Get(
						$this->hashids['evolution'],
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{id}/spots [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Spots\Get(
						$this->hashids['spot'],
						$this->hashids['evolution'],
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{id}/spots [POST]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Spots\Post(
						$request,
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{evolution_id}/spots/{id} [DELETE]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Spots\Delete(
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{id} [DELETE]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Delete(
						$this->connection,
						$this->elasticsearch->create(),
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{id} [PUT]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Put(
						$request,
						$this->uri,
						$this->connection,
						$this->elasticsearch->create(),
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'soulmates/{id} [PATCH]' => function() use ($seeker, $request): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Soulmate\Patch(
						$request,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'soulmates [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Soulmates\Get(
						$this->hashids,
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'soulmates [HEAD]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Soulmates\Head(
						$this->uri,
						$this->connection,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'seekers [OPTIONS]' => function() use ($request): Application\View {
				return new Endpoint\Preflight(
					new Endpoint\Seekers\Options($this->connection, $this->redis),
					$request
				);
			},
			'seekers [POST]' => function() use ($request): Application\View {
				return new Endpoint\Seekers\Post(
					$request,
					$this->connection,
					$this->rabbitMq,
					$this->cipher
				);
			},
			'seekers/me [GET]' => function() use ($seeker): Application\View {
				return new Endpoint\Seekers\Me\Get($this->connection, $seeker);
			},
			'seekers/{id} [GET]' => function() use ($seeker): Application\View {
				return new Endpoint\Seeker\Get($this->connection, $seeker);
			},
			'spots/{id} [PUT]' => function() use ($seeker, $request): Application\View {
				return new Endpoint\Spot\Put(
					$request,
					$this->connection,
					$seeker
				);
			},
			'spots/{id} [PATCH]' => function() use ($seeker, $request): Application\View {
				return new Endpoint\Spot\Patch(
					$request,
					$this->connection,
					$seeker
				);
			},
			'tokens [POST]' => function() use ($request): Application\View {
				return new Endpoint\Tokens\Post(
					$request,
					$this->connection,
					$this->cipher
				);
			},
			'tokens [DELETE]' => static function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Tokens\Delete(),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'refresh_tokens [POST]' => static function() use ($request): Application\View {
				return new Endpoint\RefreshTokens\Post($request);
			},
		];
	}
}
