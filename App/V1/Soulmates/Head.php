<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Soulmates;

use Elasticsearch;
use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output\EmptyFormat;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Head implements Application\View {
	private $url;
	private $database;
	private $seeker;
	private $role;
	private $elasticsearch;

	public function __construct(
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Access\User $seeker,
		Http\Role $role,
		Elasticsearch\Client $elasticsearch
	) {
		$this->url = $url;
		$this->database = $database;
		$this->seeker = $seeker;
		$this->role = $role;
		$this->elasticsearch = $elasticsearch;
	}

	public function response(array $parameters): Application\Response {
		try {
			$count = (new Domain\Search\SuitedSoulmates(
				$parameters['demand_id'],
				$this->seeker,
				$this->elasticsearch,
				$this->database
			))->count(new Dataset\EmptySelection());
			return new Response\PaginatedResponse(
				new Response\JsonApiAuthentication(
					new Response\PlainResponse(
						new EmptyFormat(),
						['X-Total-Count' => $count]
					),
					$this->role
				),
				$parameters['page'],
				new UI\AttainablePagination(
					$parameters['page'],
					$parameters['per_page'],
					$count
				),
				$this->url
			);
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
