<?php
declare(strict_types = 1);
namespace FindMyFriends\Misc;

use Klapuch\Output;

/**
 * Format moving keys by your choice
 */
final class MovingFormat implements Output\Format {
	private $origin;
	private $source;
	private $moves;

	public function __construct(Output\Format $origin, array $source, array $moves) {
		$this->origin = $origin;
		$this->source = $source;
		$this->moves = $moves;
	}

	public function with($tag, $content = null): Output\Format {
		return $this->origin->with($tag, $content);
	}

	public function serialization(): string {
		return (new Output\FilledFormat(
			$this->origin,
			$this->copy($this->moves($this->source, $this->moves), $this->source)
		))->serialization();
	}

	public function adjusted($tag, callable $adjustment): Output\Format {
		return $this->origin->adjusted($tag, $adjustment);
	}

	/**
	 * Copy moves to source
	 * @param array $moves
	 * @param array $source
	 * @return array
	 */
	private function copy(array $moves, array $source): array {
		return array_reduce(
			array_keys($moves),
			function(array $copies, $field) use ($source, $moves): array {
				if (is_array($moves[$field]))
					$copies[$field] = $this->copy($moves[$field], $source);
				else
					$copies[$moves[$field]] = $source[$moves[$field]];
				return $copies;
			},
			[]
		);
	}

	private function moves(array $source, array $moves): array {
		return array_filter(
			array_replace_recursive($source, $moves),
			function($key) use ($moves): bool {
				return is_int($key) || is_array($moves[$key] ?? null);
			},
			ARRAY_FILTER_USE_KEY
		);
	}
}