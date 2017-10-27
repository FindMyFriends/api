<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demand;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Request;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Uri;
use Klapuch\Validation;
use Predis;

final class Put implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/put.json';
	private $request;
	private $url;
	private $database;
	private $redis;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		\PDO $database,
		Predis\ClientInterface $redis
	) {
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->redis = $redis;
	}

	public function template(array $parameters): Output\Template {
		try {
			(new Domain\HarnessedDemand(
				new Domain\ExistingDemand(
					new Domain\StoredDemand(
						$parameters['id'],
						$this->database
					),
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(404)
			))->reconsider(
				(new Validation\ChainedRule(
					new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
					new Constraint\DemandRule($this->database)
				))->apply(
					json_decode(
						(new Request\ConcurrentlyControlledRequest(
							new Request\CachedRequest($this->request),
							$this->url,
							new Http\ETagRedis($this->redis)
						))->body()->serialization(),
						true
					)
				)
			);
			return new Application\RawTemplate(new Response\EmptyResponse());
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}