<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint;

use FindMyFriends\Response;
use Klapuch\Application;

final class Preflight implements Application\View {
	/** @var \Klapuch\Application\View */
	private $origin;

	/** @var \Klapuch\Application\Request */
	private $request;

	public function __construct(Application\View $origin, Application\Request $request) {
		$this->origin = $origin;
		$this->request = $request;
	}

	public function response(array $parameters): Application\Response {
		if ($this->preflight($this->request->headers()))
			return new Response\EmptyResponse();
		return $this->origin->response($parameters);
	}

	private function preflight(array $headers): bool {
		return isset(
			$headers['Access-Control-Request-Method'],
			$headers['Access-Control-Request-Headers'],
			$headers['Origin']
		);
	}
}
