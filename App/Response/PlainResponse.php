<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

final class PlainResponse implements Application\Response {
	private $format;
	private $headers;

	public function __construct(Output\Format $format, array $headers = []) {
		$this->format = $format;
		$this->headers = $headers;
	}

	public function body(): Output\Format {
		return $this->format;
	}

	public function headers(): array {
		return $this->headers;
	}
}