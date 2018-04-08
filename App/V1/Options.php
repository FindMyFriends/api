<?php
declare(strict_types = 1);

namespace FindMyFriends\V1;

use FindMyFriends\Response;
use Klapuch\Application;

final class Options implements Application\View {
	public function response(array $parameters): Application\Response {
		return new Response\EmptyResponse();
	}
}
