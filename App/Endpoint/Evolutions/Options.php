<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Evolutions;

use FindMyFriends\Domain\Access;
use FindMyFriends\Response;
use FindMyFriends\Schema;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;
use Predis;

final class Options implements Application\View {
	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	/** @var \Predis\ClientInterface */
	private $redis;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	public function __construct(
		Storage\MetaPDO $database,
		Predis\ClientInterface $redis,
		Access\Seeker $seeker
	) {
		$this->database = $database;
		$this->redis = $redis;
		$this->seeker = $seeker;
	}

	public function response(array $parameters): Application\Response {
		return new Response\JsonResponse(
			new Response\PlainResponse(
				new Output\Json([
					'options' => (new Schema\Description\ExplainedTableEnums(
						$this->database,
						$this->redis
					))->values(),
					'columns' => (new Schema\Evolution\PrioritizedColumns(
						$this->database,
						$this->seeker
					))->values(),
				])
			)
		);
	}
}
