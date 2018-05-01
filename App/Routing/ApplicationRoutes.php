<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use Elasticsearch;
use FindMyFriends\Http;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Application;
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
	private $hashids;

	public function __construct(
		Uri\Uri $uri,
		Storage\MetaPDO $database,
		Predis\ClientInterface $redis,
		Elasticsearch\Client $elasticsearch,
		PhpAmqpLib\Connection\AMQPLazyConnection $rabbitMq,
		array $hashids
	) {
		$this->uri = $uri;
		$this->database = $database;
		$this->redis = $redis;
		$this->elasticsearch = $elasticsearch;
		$this->rabbitMq = $rabbitMq;
		$this->hashids = $hashids;
	}

	public function matches(): array {
		$user = (new Access\ApiEntrance(
			$this->database
		))->enter((new Application\PlainRequest())->headers());
		return [
			'v1/demands [OPTIONS]' => new V1\Preflight(
				new V1\Demands\Options($this->database, $this->redis),
				new Application\PlainRequest()
			),
			'v1/demands?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?.+) [GET]' => new V1\Demands\Get(
				$this->hashids['demand']['hashid'],
				$this->hashids['soulmate']['hashid'],
				$this->uri,
				$this->database,
				$user,
				new Http\ChosenRole($user, ['member', 'guest'])
			),
			'v1/demands/{id} [GET]' => new V1\Demand\Get(
				$this->hashids['demand']['hashid'],
				$this->hashids['soulmate']['hashid'],
				$this->uri,
				$this->database,
				$user,
				new Http\ChosenRole($user, ['member', 'guest'])
			),
			'v1/demands/{demand_id}/soulmate_requests?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?\w+) [GET]' => new V1\Demand\SoulmateRequests\Get(
				$this->uri,
				$this->database,
				new Http\ChosenRole($user, ['member', 'guest'])
			),
			'v1/demands/{demand_id}/soulmate_requests [POST]' => new V1\Demand\SoulmateRequests\Post(
				$this->uri,
				$this->database,
				$this->rabbitMq
			),
			'v1/demands [POST]' => new V1\Demands\Post(
				$this->hashids['demand']['hashid'],
				new Application\PlainRequest(),
				$this->uri,
				$this->database,
				$this->rabbitMq,
				$user
			),
			'v1/demands/{id} [PUT]' => new V1\Demand\Put(
				new Application\PlainRequest(),
				$this->uri,
				$this->database,
				$user
			),
			'v1/demands/{id} [DELETE]' => new V1\Demand\Delete(
				$this->database,
				$user
			),
			'v1/evolutions [OPTIONS]' => new V1\Preflight(
				new V1\Evolutions\Options($this->database, $this->redis),
				new Application\PlainRequest()
			),
			'v1/evolutions [POST]' => new V1\Evolutions\Post(
				$this->hashids['evolution']['hashid'],
				new Application\PlainRequest(),
				$this->uri,
				$this->database,
				$this->elasticsearch,
				$user
			),
			'v1/evolutions?page=(1 \d+)&per_page=(10 \d+) [GET]' => new V1\Evolutions\Get(
				$this->hashids['evolution']['hashid'],
				$this->uri,
				$this->database,
				$user,
				new Http\ChosenRole($user, ['member', 'guest'])
			),
			'v1/evolutions/{id} [DELETE]' => new V1\Evolution\Delete(
				$this->database,
				$this->elasticsearch,
				$user
			),
			'v1/evolutions/{id} [PUT]' => new V1\Evolution\Put(
				new Application\PlainRequest(),
				$this->uri,
				$this->database,
				$this->elasticsearch,
				$user
			),
			'v1/soulmates/{id} [PATCH]' => new V1\Soulmate\Patch(
				new Application\PlainRequest(),
				$this->database,
				$user
			),
			'v1/demands/{demand_id}/soulmates?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?\w+) [GET]' => new V1\Soulmates\Get(
				$this->hashids,
				$this->uri,
				$this->database,
				$user,
				new Http\ChosenRole($user, ['member', 'guest']),
				$this->elasticsearch
			),
			'v1/.+ [OPTIONS]' => new V1\Options(),
		];
	}
}
