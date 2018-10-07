<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Soulmates;

use FindMyFriends\Constraint;
use FindMyFriends\Domain\Access\Seeker;
use FindMyFriends\Domain\Search;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Head implements Application\View {
	public const SCHEMA = __DIR__ . '/schema/get.json';

	/** @var \Klapuch\Uri\Uri */
	private $url;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Uri\Uri $url,
		Storage\Connection $connection,
		Seeker $seeker
	) {
		$this->url = $url;
		$this->connection = $connection;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		$count = (new Search\OwnedSoulmates(
			$this->seeker,
			$this->connection
		))->count(
			new Constraint\SchemaFilter(
				new Dataset\RestFilter($parameters),
				new \SplFileInfo(self::SCHEMA)
			)
		);
		return new Response\PaginatedResponse(
			new Response\PlainResponse(
				new Output\EmptyFormat(),
				['X-Total-Count' => $count, 'Content-Type' => 'text/plain']
			),
			$parameters['page'],
			new UI\AttainablePagination(
				$parameters['page'],
				$parameters['per_page'],
				$count
			),
			$this->url
		);
	}
}
