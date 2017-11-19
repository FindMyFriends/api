<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Demands;

use FindMyFriends\Domain;
use FindMyFriends\Http;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	private const ALLOWED_SORTS = ['created_at'];
	private $url;
	private $database;
	private $role;

	public function __construct(Uri\Uri $url, \PDO $database, Http\Role $role) {
		$this->url = $url;
		$this->database = $database;
		$this->role = $role;
	}

	public function template(array $parameters): Output\Template {
		try {
			$demands = new Domain\FormattedDemands(
				new Domain\CollectiveDemands(
					new Domain\FakeDemands(),
					$this->database
				)
			);
			return new Application\RawTemplate(
				new Response\PaginatedResponse(
					new Response\JsonResponse(
						new Response\JsonApiAuthentication(
							new Response\PlainResponse(
								new Misc\JsonPrintedObjects(
									...iterator_to_array(
										$demands->all(
											new Dataset\CombinedSelection(
												new Dataset\SqlRestSort(
													$parameters['sort'],
													self::ALLOWED_SORTS
												),
												new Dataset\SqlPaging(
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
						$demands->count(new Dataset\EmptySelection())
					),
					$this->url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}