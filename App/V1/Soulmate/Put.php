<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Soulmate;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Search;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Request;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Uri;

final class Put implements Application\View {
	private const SCHEMA = __DIR__ . '/schema/put.json';
	private $request;
	private $url;
	private $database;
	private $seeker;

	public function __construct(
		Application\Request $request,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Access\User $seeker
	) {
		$this->request = $request;
		$this->url = $url;
		$this->database = $database;
		$this->seeker = $seeker;
	}

	public function template(array $parameters): Output\Template {
		try {
			(new Search\ChainedSoulmate(
				new Search\HarnessedSoulmate(
					new Search\ExistingSoulmate(
						new Search\FakeSoulmate(),
						$parameters['id'],
						$this->database
					),
					new Misc\ApiErrorCallback(HTTP_NOT_FOUND)
				),
				new Search\HarnessedSoulmate(
					new Search\OwnedSoulmate(
						new Search\FakeSoulmate(),
						$parameters['id'],
						$this->seeker,
						$this->database
					),
					new Misc\ApiErrorCallback(HTTP_FORBIDDEN)
				),
				new Search\StoredSoulmate($parameters['id'], $this->database)
			))->clarify(
				(new Constraint\StructuredJson(
					new \SplFileInfo(self::SCHEMA)
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
			return new Application\RawTemplate(new Response\EmptyResponse());
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}