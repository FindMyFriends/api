<?php
declare(strict_types = 1);

namespace FindMyFriends\Mail\Verification;

use Dasuos\Mail;
use FindMyFriends\Domain\Access;
use Klapuch\Output;
use Klapuch\Storage;

/**
 * Message for verification
 */
final class Message implements Mail\Message {
	private const CONTENT = __DIR__ . '/content/verification.xsl',
		SCHEMA = __DIR__ . '/content/verification.xsd';
	private $origin;

	public function __construct(string $receiver, Storage\MetaPDO $database) {
		$this->origin = new Mail\HtmlMessage(
			(new Output\XsltTemplate(
				self::CONTENT,
				(new Output\ValidXml(
					(new Access\ReserveVerificationCodes($database))
						->generate($receiver)
						->print(new Output\Xml([], 'verification')),
					self::SCHEMA
				))
			))->render()
		);
	}

	public function headers(): array {
		return $this->origin->headers();
	}

	public function content(): string {
		return $this->origin->content();
	}
}
