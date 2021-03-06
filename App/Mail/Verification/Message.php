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

	/** @var \Dasuos\Mail\HtmlMessage */
	private $origin;

	/**
	 * @throws \UnexpectedValueException
	 */
	public function __construct(string $receiver, Storage\Connection $connection) {
		$this->origin = new Mail\HtmlMessage(
			(new Output\XsltTemplate(
				self::CONTENT,
				(new Output\ValidXml(
					(new Access\ReserveVerificationCodes($connection))
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
