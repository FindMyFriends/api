<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Evolution change which belongs only to me
 */
final class PermittedChange implements Change {
	/** @var \FindMyFriends\Domain\Evolution\Change */
	private $origin;

	/** @var int */
	private $id;

	/** @var \PDO */
	private $database;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	public function __construct(
		Change $origin,
		int $id,
		Access\Seeker $owner,
		\PDO $database
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->database = $database;
		$this->owner = $owner;
	}

	/**
	 * @param array $changes
	 * @throws \UnexpectedValueException
	 */
	public function affect(array $changes): void {
		if (!$this->permitted($this->id))
			throw $this->exception($this->id);
		$this->origin->affect($changes);
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function revert(): void {
		if (!$this->permitted($this->id))
			throw $this->exception($this->id);
		$this->origin->revert();
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if (!$this->permitted($this->id))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	private function permitted(int $id): bool {
		return (new Storage\NativeQuery(
			$this->database,
			'SELECT is_evolution_permitted(:evolution, :seeker)',
			['evolution' => $id, 'seeker' => $this->owner->id()]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'You are not permitted to see this evolution change.',
			0,
			new \UnexpectedValueException(sprintf('Evolution change %d is not permitted.', $id))
		);
	}
}
