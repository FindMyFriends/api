<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Verification code which always exists
 */
final class ExistingVerificationCode implements VerificationCode {
	/** @var \FindMyFriends\Domain\Access\VerificationCode */
	private $origin;

	/** @var string */
	private $code;

	/** @var \Klapuch\Storage\MetaPDO */
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

	/**
	 * @throws \UnexpectedValueException
	 */
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

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if (!$this->exists($this->code))
			throw new \UnexpectedValueException('The verification code does not exist');
		return $this->origin->print($format)->with('code', $this->code);
	}
}
