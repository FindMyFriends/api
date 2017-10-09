<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use FindMyFriends\Http;
use Klapuch\Application;
use Klapuch\Output;
use Klapuch\UI;
use Klapuch\Uri;

/**
 * Response suited for pagination
 * Returning 206 status code for not last page
 */
final class PartialResponse implements Application\Response {
	private $origin;
	private $page;
	private $pagination;
	private $uri;

	public function __construct(
		Application\Response $origin,
		int $page,
		UI\Pagination $pagination,
		Uri\Uri $uri
	) {
		$this->origin = $origin;
		$this->page = $page;
		$this->pagination = $pagination;
		$this->uri = $uri;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		$headers = [
			'Link' => $this->pagination->print(new Http\HeaderLink($this->uri))->serialization(),
		] + $this->origin->headers();
		http_response_code(
			array_slice($this->pagination->range(), -1) === [$this->page]
				? 200
				: 206
		);
		return $headers;
	}
}