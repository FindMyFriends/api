<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Search;

use Klapuch\Output;
use Klapuch\Storage;

/**
 * Always existing soulmate match
 */
final class ExistingSoulmate implements Soulmate {
	/** @var \FindMyFriends\Domain\Search\Soulmate */
	private $origin;

	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	public function __construct(Soulmate $origin, int $id, Storage\Connection $connection) {
		$this->origin = $origin;
		$this->id = $id;
		$this->connection = $connection;
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	/**
	 * @param array $clarification
	 * @throws \UnexpectedValueException
	 */
	public function clarify(array $clarification): void {
		if (!$this->exists($this->id))
			throw $this->exception($this->id);
		$this->origin->clarify($clarification);
	}

	private function exists(int $id): bool {
		return (bool) (new Storage\NativeQuery(
			$this->connection,
			'SELECT 1 FROM soulmates WHERE id = ?',
			[$id]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Soulmate does not exist',
			0,
			new \UnexpectedValueException(sprintf('Soulmate %d does not exist', $id))
		);
	}
}
