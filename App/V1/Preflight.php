<?php
declare(strict_types = 1);

namespace FindMyFriends\V1;

use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;

final class Preflight implements Application\View {
	private $origin;
	private $request;

	public function __construct(Application\View $origin, Application\Request $request) {
		$this->origin = $origin;
		$this->request = $request;
	}

	public function template(array $parameters): Output\Template {
		if ($this->preflight($this->request->headers()))
			return new Application\RawTemplate(new Response\EmptyResponse());
		return $this->origin->template($parameters);
	}

	private function preflight(array $headers): bool {
		return isset(
			$headers['Access-Control-Request-Method'],
			$headers['Access-Control-Request-Headers'],
			$headers['Origin']
		);
	}
}