<?php
declare(strict_types = 1);

namespace FindMyFriends\Misc;

use Klapuch\Sql\AnsiUpdate;
use Klapuch\Storage;

final class SampleSeeker implements Sample {
	private $samples;
	private $database;

	public function __construct(Storage\MetaPDO $database, array $samples = []) {
		$this->samples = $samples;
		$this->database = $database;
	}

	public function try(): array {
		['id' => $seeker] = (new SamplePostgresData($this->database, 'seeker', $this->samples))->try();
		if (isset($this->samples['verification_code'])) {
			(new Storage\BuiltQuery(
				$this->database,
				(new AnsiUpdate('verification_codes'))
					->set($this->samples['verification_code'])
					->where('seeker_id = ?', [$seeker])
			))->execute();
		}
		unset($this->samples['verification_code']);
		return [
			'id' => $seeker,
			'verification_code' => (new Storage\TypedQuery(
				$this->database,
				'SELECT * FROM verification_codes WHERE seeker_id = ?',
				[$seeker]
			))->row(),
		];
	}
}
