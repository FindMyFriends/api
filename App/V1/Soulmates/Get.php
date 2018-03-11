<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Soulmates;

use Elasticsearch;
use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	private const ALLOWED_FILTERS = ['demand_id'];
	private $hashids;
	private $url;
	private $database;
	private $seeker;
	private $role;
	private $elasticsearch;

	public function __construct(
		array $hashids,
		Uri\Uri $url,
		Storage\MetaPDO $database,
		Access\User $seeker,
		Http\Role $role,
		Elasticsearch\Client $elasticsearch
	) {
		$this->hashids = $hashids;
		$this->url = $url;
		$this->database = $database;
		$this->seeker = $seeker;
		$this->role = $role;
		$this->elasticsearch = $elasticsearch;
	}

	public function template(array $parameters): Output\Template {
		try {
			$soulmates = new Domain\Search\PublicSoulmates(
				new Domain\Search\SuitedSoulmates(
					$this->seeker,
					$this->elasticsearch,
					$this->database
				),
				$this->hashids
			);
			return new Application\RawTemplate(
				new Response\PaginatedResponse(
					new Response\JsonResponse(
						new Response\JsonApiAuthentication(
							new Response\PlainResponse(
								new Misc\JsonPrintedObjects(
									...iterator_to_array(
										$soulmates->matches(
											new Dataset\CombinedSelection(
												new Dataset\RestFilter(
													$parameters,
													self::ALLOWED_FILTERS
												),
												new Dataset\RestPaging(
													$parameters['page'],
													$parameters['per_page']
												)
											)
										)
									)
								)
							),
							$this->role
						)
					),
					$parameters['page'],
					new UI\AttainablePagination(
						$parameters['page'],
						$parameters['per_page'],
						$soulmates->count(
							new Dataset\RestFilter(
								$parameters,
								self::ALLOWED_FILTERS
							)
						)
					),
					$this->url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}