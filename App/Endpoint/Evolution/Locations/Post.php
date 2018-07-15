<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolution\Locations;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access;
use FindMyFriends\Domain\Evolution;
use FindMyFriends\Http\CreatedResourceUrl;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Hashids\HashidsInterface;
use Klapuch\Application;
use Klapuch\Storage;
use Klapuch\Uri;

final class Post implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/post.json';

	/** @var \Hashids\HashidsInterface */
	private $locationHashids;

	/** @var \Hashids\HashidsInterface */
	private $evolutionHashids;

	/** @var \Klapuch\Application\Request */
	private $request;

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		HashidsInterface $locationHashids,
		HashidsInterface $evolutionHashids,
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Access\Seeker $seeker
	) {
		$this->locationHashids = $locationHashids;
		$this->evolutionHashids = $evolutionHashids;
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
		(new Evolution\PublicLocations(
			new Evolution\HarnessedLocations(
				new Evolution\OwnedChangeLocations(
					new Evolution\ChangeLocations($parameters['id'], $this->database),
					$this->seeker,
					$parameters['id'],
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
			),
			$this->locationHashids,
			$this->evolutionHashids
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
