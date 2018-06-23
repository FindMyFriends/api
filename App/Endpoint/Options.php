<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint;

use FindMyFriends\Response;
use Klapuch\Application;

final class Options implements Application\View {
	public function response(array $parameters): Application\Response {
		return new Response\EmptyResponse();
	}
}