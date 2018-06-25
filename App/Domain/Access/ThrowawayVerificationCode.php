<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Verification code which can be used just once
 */
final class ThrowawayVerificationCode implements VerificationCode {
	/** @var string */
	private $code;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(string $code, Storage\MetaPDO $database) {
		$this->code = $code;
		$this->database = $database;
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function use(): void {
		if ($this->used())
			throw new \UnexpectedValueException('Verification code was already used');
		(new Storage\TypedQuery(
			$this->database,
			'UPDATE verification_codes
			SET used_at = NOW()
			WHERE code IS NOT DISTINCT FROM ?',
			[$this->code]
		))->execute();
	}

	private function used(): bool {
		return (bool) (new Storage\TypedQuery(
			$this->database,
			'SELECT 1
			FROM verification_codes
			WHERE code IS NOT DISTINCT FROM ?
			AND used_at IS NOT NULL',
			[$this->code]
		))->field();
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if ($this->used())
			throw new \UnexpectedValueException('Verification code was already used');
		return $format->with('code', $this->code);
	}
}
