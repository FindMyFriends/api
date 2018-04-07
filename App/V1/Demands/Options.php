<?php
declare(strict_types = 1);

namespace FindMyFriends\V1\Demands;

use FindMyFriends\Response;
use FindMyFriends\Schema;
use Klapuch\Application;
use Klapuch\Output;
use Predis;

final class Options implements Application\View {
	private $database;
	private $redis;

	public function __construct(\PDO $database, Predis\ClientInterface $redis) {
		$this->database = $database;
		$this->redis = $redis;
	}

	public function response(array $parameters): Application\Response {
		return new Response\JsonResponse(
			new Response\PlainResponse(
				new Output\Json(
					(new Schema\Demand\ExplainedTableEnums(
						$this->database,
						$this->redis
					))->values()
				)
			)
		);
	}
}