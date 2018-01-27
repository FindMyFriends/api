<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Evolutions;

use FindMyFriends\Response;
use FindMyFriends\Schema;
use Klapuch\Application;
use Klapuch\Output;

final class Options implements Application\View {
	private $database;

	public function __construct(\PDO $database) {
		$this->database = $database;
	}

	public function template(array $parameters): Output\Template {
		return new Application\RawTemplate(
			new Response\JsonResponse(
				new Response\PlainResponse(
					new Output\Json(
						(new Schema\Description\ExplainedTableEnums(
							$this->database
						))->values()
					)
				)
			)
		);
	}
}