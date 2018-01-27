<?php
declare(strict_types = 1);

namespace FindMyFriends\V1;

use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;

final class Options implements Application\View {
	public function template(array $parameters): Output\Template {
		return new Application\RawTemplate(
			new Response\EmptyResponse(
				[
					'Content-Type' => 'text/plain',
					'Content-Length' => 0,
				]
			)
		);
	}
}