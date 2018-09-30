<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand\Spots;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Interaction;
use FindMyFriends\Domain\Place;
use FindMyFriends\Http\CreatedResourceUrl;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Internal;
use Klapuch\Storage;
use Klapuch\Uri;
use Klapuch\Validation;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		Storage\Connection $connection,
		Access\Seeker $seeker
	) {
		$this->request = $request;
		$this->url = $url;
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	/**
	 * @param array $parameters
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Application\Response
	 */
	public function response(array $parameters): Application\Response {
		(new Place\ChainedSpots(
			new Place\HarnessedSpots(
				new Interaction\OwnedSpots(
					new Place\FakeSpots(),
					$this->seeker,
					$parameters['id'],
					$this->connection
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Interaction\DemandSpots($parameters['id'], $this->connection)
		))->track(
			(new Validation\ChainedRule(
				new Constraint\StructuredJson(new \SplFileInfo(self::SCHEMA)),
				new Constraint\SpotRule()
			))->apply((new Internal\DecodedJson($this->request->body()->serialization()))->values())
		);
		return new Response\CreatedResponse(
			new Response\EmptyResponse(),
			new CreatedResourceUrl(
				new Uri\RelativeUrl($this->url, $this->url->path()),
				[]
			)
		);
	}
}
