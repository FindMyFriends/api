<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use FindMyFriends\Domain\Access;
use FindMyFriends\Elasticsearch\LazyElasticsearch;
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
		Storage\MetaPDO $database,
		Predis\ClientInterface $redis,
		LazyElasticsearch $elasticsearch,
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
		$seeker = (new Access\HarnessedEntrance(
			new Access\RateLimitedEntrance(
				new Access\ApiEntrance($this->database),
				$this->redis
			),
			new Misc\ApiErrorCallback(HTTP_TOO_MANY_REQUESTS)
		))->enter((new Application\PlainRequest())->headers());
		return [
			'activations [POST]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Activations\Post(
						new Application\PlainRequest(),
						$this->database
					),
					new Http\ChosenRole($seeker, ['guest'])
				);
			},
			'descriptions [OPTIONS]' => function(): Application\View {
				return new Endpoint\Preflight(
					new Endpoint\Descriptions\Options($this->database, $this->redis),
					new Application\PlainRequest()
				);
			},
			'demands [OPTIONS]' => function(): Application\View {
				return new Endpoint\Preflight(
					new Endpoint\Demands\Options($this->database, $this->redis),
					new Application\PlainRequest()
				);
			},
			'demands [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demands\Get(
						$this->hashids['demand'],
						$this->uri,
						$this->database,
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
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{demand_id}/soulmate_requests [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\SoulmateRequests\Get(
						$this->uri,
						$this->database
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{demand_id}/soulmate_requests [POST]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\SoulmateRequests\Post(
						$this->uri,
						$this->database,
						$this->rabbitMq
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands [POST]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demands\Post(
						$this->hashids['demand'],
						new Application\PlainRequest(),
						$this->uri,
						$this->database,
						$this->rabbitMq,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id} [PUT]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Put(
						new Application\PlainRequest(),
						$this->uri,
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id} [PATCH]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Patch(
						new Application\PlainRequest(),
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id} [DELETE]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Delete(
						$this->database,
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
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{id}/spots [POST]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Spots\Post(
						new Application\PlainRequest(),
						$this->uri,
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{demand_id}/spots/{id} [DELETE]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Spots\Delete(
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions [OPTIONS]' => function() use ($seeker): Application\View {
				return new Endpoint\Preflight(
					new View\AuthenticatedView(
						new Endpoint\Evolutions\Options(
							$this->database,
							$this->redis,
							$seeker
						),
						new Http\ChosenRole($seeker, ['member'])
					),
					new Application\PlainRequest()
				);
			},
			'evolutions [POST]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolutions\Post(
						$this->hashids['evolution'],
						new Application\PlainRequest(),
						$this->uri,
						$this->database,
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
						$this->database,
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
						$this->database,
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
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{id}/spots [POST]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Spots\Post(
						new Application\PlainRequest(),
						$this->uri,
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{evolution_id}/spots/{id} [DELETE]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Spots\Delete(
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{id} [DELETE]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Delete(
						$this->database,
						$this->elasticsearch->create(),
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'evolutions/{id} [PUT]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Evolution\Put(
						new Application\PlainRequest(),
						$this->uri,
						$this->database,
						$this->elasticsearch->create(),
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'soulmates/{id} [PATCH]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Soulmate\Patch(
						new Application\PlainRequest(),
						$this->database,
						$seeker
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{demand_id}/soulmates [GET]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Soulmates\Get(
						$this->hashids,
						$this->uri,
						$this->database,
						$this->elasticsearch->create()
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'demands/{demand_id}/soulmates [HEAD]' => function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Demand\Soulmates\Head(
						$this->uri,
						$this->database,
						$this->elasticsearch->create()
					),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'seekers [OPTIONS]' => function(): Application\View {
				return new Endpoint\Preflight(
					new Endpoint\Seekers\Options($this->database, $this->redis),
					new Application\PlainRequest()
				);
			},
			'seekers [POST]' => function(): Application\View {
				return new Endpoint\Seekers\Post(
					new Application\PlainRequest(),
					$this->database,
					$this->rabbitMq,
					$this->cipher
				);
			},
			'spots/{id} [PUT]' => function() use ($seeker): Application\View {
				return new Endpoint\Spot\Put(
					new Application\PlainRequest(),
					$this->database,
					$seeker
				);
			},
			'spots/{id} [PATCH]' => function() use ($seeker): Application\View {
				return new Endpoint\Spot\Patch(
					new Application\PlainRequest(),
					$this->database,
					$seeker
				);
			},
			'tokens [POST]' => function(): Application\View {
				return new Endpoint\Tokens\Post(
					new Application\PlainRequest(),
					$this->database,
					$this->cipher
				);
			},
			'tokens [DELETE]' => static function() use ($seeker): Application\View {
				return new View\AuthenticatedView(
					new Endpoint\Tokens\Delete(),
					new Http\ChosenRole($seeker, ['member'])
				);
			},
			'refresh_tokens [POST]' => function(): Application\View {
				return new Endpoint\RefreshTokens\Post(
					new Application\PlainRequest(),
					$this->database
				);
			},
		];
	}
}
