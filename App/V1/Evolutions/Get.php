<?php
declare(strict_types = 1);
namespace FindMyFriends\V1\Evolutions;

use FindMyFriends\Domain\Evolution;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Dataset;
use Klapuch\Output;
use Klapuch\UI;
use Klapuch\Uri;

final class Get implements Application\View {
	private $url;
	private $database;
	private $user;

	public function __construct(Uri\Uri $url, \PDO $database, Access\User $user) {
		$this->url = $url;
		$this->database = $database;
		$this->user = $user;
	}

	public function template(array $parameters): Output\Template {
		try {
			$evolution = new Evolution\FormattedChain(
				new Evolution\IndividualChain(
					$this->user,
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
										$evolution->changes(
											new Dataset\CombinedSelection(
												new Dataset\SqlPaging(
													$parameters['page'],
													$parameters['per_page']
												)
											)
										)
									)
								)
							),
							$this->user,
							$this->url
						)
					),
					$parameters['page'],
					new UI\AttainablePagination(
						$parameters['page'],
						$parameters['per_page'],
						$evolution->count(new Dataset\EmptySelection())
					),
					$this->url
				)
			);
		} catch (\UnexpectedValueException $ex) {
			return new Application\RawTemplate(new Response\JsonError($ex));
		}
	}
}