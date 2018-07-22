<?php
declare(strict_types = 1);

namespace FindMyFriends\Scheduling\Task;

use FindMyFriends\Scheduling;

final class GenerateNginxConfiguration implements Scheduling\Job {
	/** @var \SplFileInfo */
	private $destination;

	public function __construct(\SplFileInfo $destination) {
		$this->destination = $destination;
	}

	public function fulfill(): void {
		$preflightHeaders = file_get_contents(__DIR__ . '/../../../docker/nginx/preflight_headers.conf');
		$securityHeaders = file_get_contents(__DIR__ . '/../../../docker/nginx/security_headers.conf');
		file_put_contents(
			$this->destination->getPathname(),
			<<<CONF
# Automatically generated, do not manually edit
if (\$request_method = OPTIONS) {
{$preflightHeaders}
{$securityHeaders}
	add_header Content-Type text/plain;
	add_header Content-Length 0;
	return 204;
}
CONF
		);
	}

	public function name(): string {
		return 'GenerateNginxConfiguration';
	}
}
