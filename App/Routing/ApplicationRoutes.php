<?php
declare(strict_types = 1);

namespace FindMyFriends\Routing;

use FindMyFriends\Http;
use FindMyFriends\V1;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Routing;
use Klapuch\Uri;
use Predis;

/**
 * Routes for whole application
 */
final class ApplicationRoutes implements Routing\Routes {
	private $uri;
	private $database;
	private $redis;
	private $hashids;

	public function __construct(Uri\Uri $uri, \PDO $database, Predis\ClientInterface $redis, array $hashids) {
		$this->uri = $uri;
		$this->database = $database;
		$this->redis = $redis;
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
			'v1/demands?page=(1 \d+)&per_page=(10 \d+)&sort=( ([-\s])?\w+) [GET]' => new V1\Demands\Get(
				$this->hashids['demand']['hashid'],
				$this->uri,
				$this->database,
				new Http\ChosenRole($user, ['member', 'guest'])
			),
			'v1/demands/{id} [GET]' => new V1\Demand\Get(
				$this->hashids['demand']['hashid'],
				$this->uri,
				$this->database,
				new Http\ChosenRole($user, ['member', 'guest'])
			),
			'v1/demands [POST]' => new V1\Demands\Post(
				$this->hashids['demand']['hashid'],
				new Application\PlainRequest(),
				$this->uri,
				$this->database,
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
			'v1/evolutions?page=(1 \d+)&per_page=(10 \d+) [GET]' => new V1\Evolutions\Get(
				$this->hashids['evolution']['hashid'],
				$this->uri,
				$this->database,
				$user,
				new Http\ChosenRole($user, ['member', 'guest'])
			),
			'v1/evolutions/{id} [DELETE]' => new V1\Evolution\Delete(
				$this->database,
				$user
			),
			'v1/.+ [OPTIONS]' => new V1\Options(),
		];
	}
}