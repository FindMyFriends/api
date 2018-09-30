<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Sql\AnsiUpdate;
use Klapuch\Storage;

final class SampleSeeker implements Sample {
	/** @var mixed[] */
	private $samples;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Storage\Connection $connection, array $samples = []) {
		$this->samples = $samples;
		$this->connection = $connection;
	}

	public function try(): array {
		['id' => $seeker] = (new SamplePostgresData($this->connection, 'seeker', $this->samples))->try();
		if (isset($this->samples['verification_code'])) {
			(new Storage\BuiltQuery(
				$this->connection,
				(new AnsiUpdate('verification_codes'))
					->set($this->samples['verification_code'])
					->where('seeker_id = ?', [$seeker])
			))->execute();
		}
		unset($this->samples['verification_code']);
		return [
			'id' => $seeker,
			'verification_code' => (new Storage\TypedQuery(
				$this->connection,
				'SELECT * FROM verification_codes WHERE seeker_id = ?',
				[$seeker]
			))->row(),
		];
	}
}
