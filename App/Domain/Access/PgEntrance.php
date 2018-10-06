<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Access;

use Klapuch\Storage;

/**
 * Entrance to PG database
 */
final class PgEntrance implements Entrance {
	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Entrance */
	private $origin;

	public function __construct(Entrance $origin, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->connection = $connection;
	}

	/**
	 * @param array $credentials
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function enter(array $credentials): Seeker {
		$seeker = $this->origin->enter($credentials);
		(new Storage\NativeQuery($this->connection, 'SELECT globals_set_seeker(?)', [$seeker->id()]))->execute();
		return $seeker;
	}

	/**
	 * @throws \UnexpectedValueException
	 * @return \FindMyFriends\Domain\Access\Seeker
	 */
	public function exit(): Seeker {
		(new Storage\NativeQuery($this->connection, 'SELECT globals_set_seeker(NULL)'))->execute();
		return $this->origin->exit();
	}
}
