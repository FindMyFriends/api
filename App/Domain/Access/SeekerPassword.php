<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Encryption;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Password which belongs to particular seeker
 */
final class SeekerPassword implements Password {
	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $seeker;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \Klapuch\Encryption\Cipher */
	private $cipher;

	public function __construct(
		Seeker $seeker,
		Storage\Connection $connection,
		Encryption\Cipher $cipher
	) {
		$this->seeker = $seeker;
		$this->connection = $connection;
		$this->cipher = $cipher;
	}

	public function change(string $password): void {
		(new Storage\TypedQuery(
			$this->connection,
			'UPDATE seekers
			SET password = ?
			WHERE id = ?',
			[$this->cipher->encryption($password), $this->seeker->id()]
		))->execute();
	}

	public function print(Output\Format $format): Output\Format {
		return $format;
	}
}
