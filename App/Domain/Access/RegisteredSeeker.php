<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Already registered seeker in the system
 */
final class RegisteredSeeker implements Seeker {
	/** @var string */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(string $id, Storage\Connection $connection) {
		$this->id = $id;
		$this->connection = $connection;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return array
	 */
	public function properties(): array {
		$seeker = (new Storage\TypedQuery(
			$this->connection,
			'SELECT *
			FROM seekers
			WHERE id IS NOT DISTINCT FROM ?',
			[$this->id()]
		))->row();
		return (new ConstantSeeker((string) $seeker['id'], $seeker))->properties();
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return string
	 */
	public function id(): string {
		if ($this->registered($this->id))
			return $this->id;
		throw new \UnexpectedValueException(
			'The seeker has not been registered yet'
		);
	}

	private function registered(string $id): bool {
		return (bool) (new Storage\TypedQuery(
			$this->connection,
			'SELECT 1
			FROM seekers
			WHERE id IS NOT DISTINCT FROM ?',
			[$id]
		))->field();
	}
}
