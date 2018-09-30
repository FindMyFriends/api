<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Evolution;

use FindMyFriends\Domain\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Evolution change which belongs only to me
 */
final class VisibleChange implements Change {
	/** @var \FindMyFriends\Domain\Evolution\Change */
	private $origin;

	/** @var int */
	private $id;

	/** @var \Klapuch\Storage\Connection */
	private $connection;

	/** @var \FindMyFriends\Domain\Access\Seeker */
	private $owner;

	public function __construct(
		Change $origin,
		int $id,
		Access\Seeker $owner,
		Storage\Connection $connection
	) {
		$this->origin = $origin;
		$this->id = $id;
		$this->connection = $connection;
		$this->owner = $owner;
	}

	/**
	 * @param array $changes
	 * @throws \UnexpectedValueException
	 */
	public function affect(array $changes): void {
		if (!$this->owned($this->id, $this->owner))
			throw $this->exception($this->id);
		$this->origin->affect($changes);
	}

	/**
	 * @throws \UnexpectedValueException
	 */
	public function revert(): void {
		if (!$this->owned($this->id, $this->owner))
			throw $this->exception($this->id);
		$this->origin->revert();
	}

	/**
	 * @param \Klapuch\Output\Format $format
	 * @throws \UnexpectedValueException
	 * @return \Klapuch\Output\Format
	 */
	public function print(Output\Format $format): Output\Format {
		if (!$this->visible($this->id, $this->owner))
			throw $this->exception($this->id);
		return $this->origin->print($format);
	}

	private function owned(int $id, Access\Seeker $owner): bool {
		return (new Storage\NativeQuery(
			$this->connection,
			'SELECT is_evolution_owned(:evolution, :seeker)',
			['evolution' => $id, 'seeker' => $owner->id()]
		))->field();
	}

	private function visible(int $id, Access\Seeker $owner): bool {
		return (new Storage\NativeQuery(
			$this->connection,
			'SELECT is_evolution_visible(:evolution, :seeker)',
			['evolution' => $id, 'seeker' => $owner->id()]
		))->field();
	}

	private function exception(int $id): \UnexpectedValueException {
		return new \UnexpectedValueException(
			'Evolution change does not belong to you.',
			0,
			new \UnexpectedValueException(sprintf('Evolution change %d does not belong to you.', $id))
		);
	}
}
