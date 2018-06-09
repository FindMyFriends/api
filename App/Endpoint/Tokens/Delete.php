<?php
declare(strict_types = 1);

namespace FindMyFriends\Endpoint\Tokens;

use FindMyFriends\Domain\Access;
use FindMyFriends\Response;
use Klapuch\Application;
use Klapuch\Output;

final class Delete implements Application\View {
	public function response(array $parameters): Application\Response {
		(new Access\TokenEntrance(new Access\FakeEntrance(new Access\Guest())))->exit();
		return new Response\PlainResponse(new Output\EmptyFormat());
	}
}
