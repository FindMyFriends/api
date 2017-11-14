<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use Klapuch\Access;
use Klapuch\Application;
use Klapuch\Authorization;
use Klapuch\Output;
use Klapuch\Uri;

final class JsonApiAuthentication implements Application\Response {
	private const PERMISSIONS = __DIR__ . '/../Configuration/Permissions/v1.xml';
	private $origin;
	private $user;
	private $uri;

	public function __construct(
		Application\Response $origin,
		Access\User $user,
		Uri\Uri $uri
	) {
		$this->origin = $origin;
		$this->user = $user;
		$this->uri = $uri;
	}

	public function body(): Output\Format {
		if ($this->allowed($this->user, $this->uri))
			return $this->origin->body();
		return new Output\Json(['message' => 'You are not allowed to see the response.']);
	}

	public function headers(): array {
		return $this->origin->headers();
	}

	public function status(): int {
		if ($this->allowed($this->user, $this->uri))
			return $this->origin->status();
		return HTTP_FORBIDDEN;
	}

	/**
	 * Does the user have access to the URI?
	 * @param \Klapuch\Access\User $user
	 * @param \Klapuch\Uri\Uri $uri
	 * @return bool
	 */
	private function allowed(Access\User $user, Uri\Uri $uri): bool {
		return (new Authorization\HttpRole(
			new Authorization\RolePermissions(
				$user->properties()['role'] ?? 'guest',
				new Authorization\XmlPermissions(self::PERMISSIONS)
			)
		))->allowed($uri->path());
	}
}