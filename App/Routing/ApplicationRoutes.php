<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use Elasticsearch;
use FindMyFriends\Domain;
use FindMyFriends\Domain\Access;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\V1;
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
	private $uri;
	private $database;
	private $redis;
	private $elasticsearch;
	private $rabbitMq;
	private $cipher;
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
			'v1/demands [OPTIONS]' => new V1\Preflight(
				new V1\Demands\Options($this->database, $this->redis),
				new Application\PlainRequest()
			),
			'v1/demands?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?.+) [GET]' => new View\AuthenticatedView(
				new V1\Demands\Get(
					$this->hashids['demand']['hashid'],
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands/{id} [GET]' => new View\AuthenticatedView(
				new V1\Demand\Get(
					$this->hashids['demand']['hashid'],
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands/{demand_id}/soulmate_requests?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?.+) [GET]' => new View\AuthenticatedView(
				new V1\Demand\SoulmateRequests\Get(
					$this->uri,
					$this->database
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands/{demand_id}/soulmate_requests [POST]' => new View\AuthenticatedView(
				new V1\Demand\SoulmateRequests\Post(
					$this->uri,
					$this->database,
					$this->rabbitMq
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands [POST]' => new View\AuthenticatedView(
				new V1\Demands\Post(
					$this->hashids['demand']['hashid'],
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$this->rabbitMq,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands/{id} [PUT]' => new View\AuthenticatedView(
				new V1\Demand\Put(
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands/{id} [PATCH]' => new View\AuthenticatedView(
				new V1\Demand\Patch(
					new Application\PlainRequest(),
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands/{id} [DELETE]' => new View\AuthenticatedView(
				new V1\Demand\Delete(
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/evolutions [OPTIONS]' => new V1\Preflight(
				new V1\Evolutions\Options($this->database, $this->redis),
				new Application\PlainRequest()
			),
			'v1/evolutions [POST]' => new View\AuthenticatedView(
				new V1\Evolutions\Post(
					$this->hashids['evolution']['hashid'],
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$this->elasticsearch,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/evolutions?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?.+) [GET]' => new View\AuthenticatedView(
				new V1\Evolutions\Get(
					$this->hashids['evolution']['hashid'],
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/evolutions/{id} [GET]' => new View\AuthenticatedView(
				new V1\Evolution\Get(
					$this->hashids['evolution']['hashid'],
					$this->uri,
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/evolutions/{id} [DELETE]' => new View\AuthenticatedView(
				new V1\Evolution\Delete(
					$this->database,
					$this->elasticsearch,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/evolutions/{id} [PUT]' => new View\AuthenticatedView(
				new V1\Evolution\Put(
					new Application\PlainRequest(),
					$this->uri,
					$this->database,
					$this->elasticsearch,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/soulmates/{id} [PATCH]' => new View\AuthenticatedView(
				new V1\Soulmate\Patch(
					new Application\PlainRequest(),
					$this->database,
					$seeker
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands/{demand_id}/soulmates?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?.+) [GET]' => new View\AuthenticatedView(
				new V1\Demand\Soulmates\Get(
					$this->hashids,
					$this->uri,
					$this->database,
					$this->elasticsearch
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/demands/{demand_id}/soulmates?page=(1 \d+)&per_page=(10 \d+) [HEAD]' => new View\AuthenticatedView(
				new V1\Demand\Soulmates\Head(
					$this->uri,
					$this->database,
					$this->elasticsearch
				),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/seekers [POST]' => new V1\Seekers\Post(
				new Application\PlainRequest(),
				$this->database,
				$this->rabbitMq,
				$this->cipher
			),
			'v1/tokens [POST]' => new V1\Tokens\Post(
				new Application\PlainRequest(),
				$this->database,
				$this->cipher
			),
			'v1/tokens [DELETE]' => new View\AuthenticatedView(
				new V1\Tokens\Delete(),
				new Http\ChosenRole($seeker, ['member'])
			),
			'v1/.+ [OPTIONS]' => new V1\Options(),
		];
	}
}
