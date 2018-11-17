<?php
declare(strict_types = 1);

namespace FindMyFriends\Domain\Activity;

use Klapuch\Output;

/**
 * Notification formatted to be used for public representation
 */
final class PublicNotification implements Notification {
	/** @var \FindMyFriends\Domain\Activity\Notification */
	private $origin;

	public function __construct(Notification $origin) {
		$this->origin = $origin;
	}

	public function receive(Output\Format $format): Output\Format {
		return $this->origin->receive($format)
			->adjusted('seen_at', static function (?string $datetime): ?string {
				return $datetime === null ? $datetime : (new \DateTime($datetime))->format(\DateTime::ATOM);
			})->adjusted('notified_at', static function (string $datetime): string {
				return (new \DateTime($datetime))->format(\DateTime::ATOM);
			});
	}

	public function seen(): void {
		$this->origin->seen();
	}

	public function unseen(): void {
		$this->origin->unseen();
	}
}
