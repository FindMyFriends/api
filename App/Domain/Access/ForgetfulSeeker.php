<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Forgetful seeker is the one who forget password
 */
final class ForgetfulSeeker implements Seeker {
	/** @var string */
	private $reminder;

	/** @var \Klapuch\Storage\MetaPDO */
	private $database;

	public function __construct(string $reminder, Storage\MetaPDO $database) {
		$this->reminder = $reminder;
		$this->database = $database;
	}

	public function properties(): array {
		$seeker = (new Storage\TypedQuery(
			$this->database,
			'SELECT *
			FROM seekers
			WHERE id IS NOT DISTINCT FROM ?',
			[(int) $this->id()]
		))->row();
		return (new ConstantSeeker(strval($seeker['id'] ?? '0'), $seeker))->properties();
	}

	public function id(): string {
		return strval(
			(int) (new Storage\TypedQuery(
				$this->database,
				'SELECT seeker_id
				FROM forgotten_passwords
				WHERE reminder IS NOT DISTINCT FROM ?',
				[$this->reminder]
			))->field()
		);
	}
}
