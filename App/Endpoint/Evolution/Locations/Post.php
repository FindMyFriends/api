<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution\Locations;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http\CreatedResourceUrl;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Storage;
use Klapuch\Uri;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';

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

	/**
	 * @param array $parameters
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Application\Response
	 */
	public function response(array $parameters): Application\Response {
		(new Evolution\ChainedLocations(
			new Evolution\HarnessedLocations(
				new Evolution\OwnedChangeLocations(
					new Evolution\FakeLocations(),
					$this->seeker,
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			new Evolution\ChangeLocations($parameters['id'], $this->database)
		))->track(
			(new Constraint\StructuredJson(
				new \SplFileInfo(self::SCHEMA)
			))->apply(json_decode($this->request->body()->serialization(), true))
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
