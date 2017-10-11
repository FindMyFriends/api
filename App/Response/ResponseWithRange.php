<?php
declare(strict_types = 1);
namespace FindMyFriends\Response;

use Klapuch\Application;
use Klapuch\Output;

/**
 * Response containing Content-Range
 */
final class ResponseWithRange implements Application\Response {
	private $origin;
	private $unit;
	private $current;
	private $perPage;
	private $total;

	public function __construct(
		Application\Response $origin,
		string $unit,
		int $current,
		int $perPage,
		int $total
	) {
		$this->origin = $origin;
		$this->unit = $unit;
		$this->current = $current;
		$this->perPage = $perPage;
		$this->total = $total;
	}

	public function body(): Output\Format {
		return $this->origin->body();
	}

	public function headers(): array {
		if ($this->overstepped($this->current, $this->perPage, $this->total))
			throw new \UnexpectedValueException('Page out of allowed range');
		return [
			'Content-Range' => sprintf(
				'%s %d-%d/%d',
				$this->unit,
				($this->current - 1) * $this->perPage,
				min($this->perPage * $this->current, $this->total - 1),
				$this->total
			),
		] + $this->origin->headers();
	}

	private function overstepped(int $current, int $perPage, int $total): bool {
		return $current * min($perPage, $total) > $total;
	}
}