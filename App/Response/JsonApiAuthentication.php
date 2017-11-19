<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use FindMyFriends\Http;
use Klapuch\Application;
use Klapuch\Output;

final class JsonApiAuthentication implements Application\Response {
	private $origin;
	private $role;

	public function __construct(Application\Response $origin, Http\Role $role) {
		$this->origin = $origin;
		$this->role = $role;
	}

	public function body(): Output\Format {
		if ($this->role->allowed())
			return $this->origin->body();
		return new Output\Json(['message' => 'You are not allowed to see the response.']);
	}

	public function headers(): array {
		return $this->origin->headers();
	}

	public function status(): int {
		if ($this->role->allowed())
			return $this->origin->status();
		return HTTP_FORBIDDEN;
	}
}