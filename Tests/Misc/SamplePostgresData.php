<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Storage;

final class SamplePostgresData implements Sample {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var string */
	private $sample;

	/** @var mixed[] */
	private $data;

	public function __construct(Storage\Connection $connection, string $sample, array $data = []) {
		$this->connection = $connection;
		$this->sample = $sample;
		$this->data = $data;
	}

	public function try(): array {
		return (new Storage\NativeQuery(
			$this->connection,
			sprintf('SELECT samples.%s(?) AS id', $this->sample),
			[json_encode($this->data, JSON_FORCE_OBJECT)]
		))->row();
	}
}
