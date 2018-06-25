<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand;

use FindMyFriends\Constraint;
use FindMyFriends\Domain;
use FindMyFriends\Domain\Access;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Request;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Storage;
use Klapuch\Uri;
use Klapuch\Validation;

final class Put implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/put.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Access\Seeker $seeker
	) {
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		(new Domain\ChainedDemand(
			new Domain\HarnessedDemand(
				new Domain\ExistingDemand(
					new Domain\FakeDemand(),
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
			),
			new Domain\HarnessedDemand(
				new Domain\OwnedDemand(
					new Domain\FakeDemand(),
					$parameters['id'],
					$this->seeker,
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Domain\StoredDemand($parameters['id'], $this->database)
		))->reconsider(
			(new Validation\ChainedRule(
				new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
				new Constraint\DemandRule()
			))->apply(
				json_decode(
					(new Request\ConcurrentlyControlledRequest(
						$this->request,
						new Http\PostgresETag($this->database, $this->url)
					))->body()->serialization(),
					true
				)
			)
		);
		return new Response\EmptyResponse();
	}
}
