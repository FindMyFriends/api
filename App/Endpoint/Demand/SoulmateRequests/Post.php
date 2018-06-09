<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Demand\SoulmateRequests;

use FindMyFriends\Domain\Search;
use FindMyFriends\Misc;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\Storage;
use Klapuch\Uri;
use PhpAmqpLib;

final class Post implements Application\View {
	private $url;
	private $database;
	private $rabbitMq;

	public function __construct(
		Uri\Uri $url,
		Storage\MetaPDO $database,
		PhpAmqpLib\Connection\AbstractConnection $rabbitMq
	) {
		$this->url = $url;
		$this->database = $database;
		$this->rabbitMq = $rabbitMq;
	}

	public function response(array $parameters): Application\Response {
		try {
			(new Search\HarnessedPublisher(
				new Search\RefreshablePublisher(
					new Search\AmqpPublisher($this->rabbitMq),
					$this->database
				),
				new Misc\ApiErrorCallback(HTTP_TOO_MANY_REQUESTS)
			))->publish($parameters['demand_id']);
			return new class ($this->url) implements Application\Response {
				private $url;

				public function __construct(Uri\Uri $url) {
					$this->url = $url;
				}

				public function body(): Output\Format {
					return new Output\EmptyFormat();
				}

				public function headers(): array {
					return [];
				}

				public function status(): int {
					return HTTP_ACCEPTED;
				}
			};
		} catch (\UnexpectedValueException $ex) {
			return new Response\JsonError($ex);
		}
	}
}
