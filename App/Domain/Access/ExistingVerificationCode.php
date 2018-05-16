<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Verification code which always exists
 */
final class ExistingVerificationCode implements VerificationCode {
	private $origin;
	private $code;
	private $database;

	public function __construct(
		VerificationCode $origin,
		string $code,
		Storage\MetaPDO $database
	) {
		$this->origin = $origin;
		$this->code = $code;
		$this->database = $database;
	}

	public function use(): void {
		if (!$this->exists($this->code))
			throw new \UnexpectedValueException('The verification code does not exist');
		$this->origin->use();
	}

	private function exists(string $code): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1
			FROM verification_codes
			WHERE code IS NOT DISTINCT FROM ?',
			[$code]
		))->field();
	}

	public function print(Output\Format $format): Output\Format {
		if (!$this->exists($this->code))
			throw new \UnexpectedValueException('The verification code does not exist');
		return $this->origin->print($format)->with('code', $this->code);
	}
}
