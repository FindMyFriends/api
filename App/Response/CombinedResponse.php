<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

final class CombinedResponse implements Application\Response {
	private $responses;

	public function __construct(Application\Response ...$responses) {
		$this->responses = $responses;
	}

	public function body(): Output\Format {
		return new Output\CombinedFormat(
			...array_map(
				function(Application\Response $response): Output\Format {
					return $response->body();
				},
				$this->responses
			)
		);
	}

	public function headers(): array {
		return array_reduce(
			$this->responses,
			function(array $headers, Application\Response $response): array {
				return $headers + $response->headers();
			},
			[]
		);
	}
}